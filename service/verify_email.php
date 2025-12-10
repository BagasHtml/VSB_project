<?php
session_start();
require __DIR__ . '/db.php';            
require __DIR__ . '/../config/config.php';
require __DIR__ . '/email_helper.php';  

/**
 * Helper kecil
 */
function now_sql() {
    return date('Y-m-d H:i:s');
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? null;
$display = [
    'status' => 'info',
    'title'  => 'Verifikasi Email',
    'message'=> '',
    'show_resend_form' => false,
    'email' => '',
];

// ====== HANDLE RESEND REQUEST (POST) ======
if ($method === 'POST' && $action === 'resend') {
    $email_post = trim($_POST['email'] ?? '');
    if ($email_post === '' || !filter_var($email_post, FILTER_VALIDATE_EMAIL)) {
        $display['status'] = 'error';
        $display['message'] = 'Email tidak valid untuk dikirim ulang.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, email_verified FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email_post);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $display['status'] = 'error';
            $display['message'] = "Email tidak ditemukan di sistem.";
        } else if ($user['email_verified'] == 1) {
            $display['status'] = 'warning';
            $display['message'] = "Akun sudah terverifikasi. Silakan login.";
        } else {
            // generate token baru
            try {
                $newToken = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                $newToken = sha1($user['email'] . time() . random_int(1000,9999));
            }

            $use_token_created_col = false;
            $check = $conn->query("SHOW COLUMNS FROM users LIKE 'token_created_at'");
            if ($check && $check->num_rows > 0) $use_token_created_col = true;

            if ($use_token_created_col) {
                $stmt = $conn->prepare("UPDATE users SET verification_token = ?, token_created_at = ? WHERE id = ?");
                $ts = now_sql();
                $stmt->bind_param("ssi", $newToken, $ts, $user['id']);
            } else {
                $stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
                $stmt->bind_param("si", $newToken, $user['id']);
            }
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $send = sendResendVerificationEmail($user['email'], $user['username'] ?? $user['email'], $newToken);
                if ($send['success']) {
                    $display['status'] = 'success';
                    $display['message'] = "Link verifikasi baru telah dikirim ke <b>" . htmlspecialchars($user['email'] ?? '', ENT_QUOTES) . "</b>. Silakan cek email (termasuk folder Spam).";
                } else {
                    $display['status'] = 'error';
                    $display['message'] = "Gagal mengirim email verifikasi: " . htmlspecialchars($send['message'] ?? 'Unknown error', ENT_QUOTES);
                    $display['show_resend_form'] = true;
                    $display['email'] = $user['email'] ?? '';
                }
            } else {
                $display['status'] = 'error';
                $display['message'] = "Gagal menyimpan token verifikasi. Coba lagi nanti.";
                $display['show_resend_form'] = true;
                $display['email'] = $user['email'] ?? '';
            }
        }
    }
}

// ====== HANDLE GET VERIFICATION ======
if ($method === 'GET') {
    $token = trim($_GET['token'] ?? '');
    $email = trim($_GET['email'] ?? '');

    if ($token === '' && $email === '') {
        $display['status'] = 'error';
        $display['message'] = 'Parameter token/email tidak ditemukan.';
        $display['show_resend_form'] = true;
        $display['email'] = $email;
    } else {
        $user = null;
        if ($email !== '') {
            $stmt = $conn->prepare("SELECT id, username, verification_token, email_verified, token_created_at FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        if (!$user && $token !== '') {
            $stmt = $conn->prepare("SELECT id, username, verification_token, email_verified, token_created_at, email FROM users WHERE verification_token = ? LIMIT 1");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($user) $email = $user['email'] ?? '';
        }

        if (!$user) {
            $display['status'] = 'error';
            $display['message'] = 'Token tidak valid atau email tidak ditemukan.';
            $display['show_resend_form'] = true;
            $display['email'] = $email;
        } else {
            if ((int)($user['email_verified'] ?? 0) === 1) {
                $display['status'] = 'warning';
                $display['message'] = 'Email ini sudah diverifikasi sebelumnya.';
            } else {
                if (empty($user['verification_token']) || $token === '' || !hash_equals($user['verification_token'], $token)) {
                    $display['status'] = 'error';
                    $display['message'] = 'Token tidak cocok atau mungkin sudah kadaluarsa.';
                    $display['show_resend_form'] = true;
                    $display['email'] = $email;
                } else {
                    $expired = false;
                    if (!empty($user['token_created_at'])) {
                        $created = strtotime($user['token_created_at']);
                        if ($created !== false && (time() - $created)/3600 > 24) $expired = true;
                    }

                    if ($expired) {
                        $display['status'] = 'error';
                        $display['message'] = 'Token sudah kedaluwarsa. Silakan minta link verifikasi baru.';
                        $display['show_resend_form'] = true;
                        $display['email'] = $email;
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL" . ((bool)$conn->query("SHOW COLUMNS FROM users LIKE 'token_created_at'")->num_rows ? ", token_created_at = NULL" : "") . " WHERE id = ?");
                        $stmt->bind_param("i", $user['id']);
                        $ok = $stmt->execute();
                        $stmt->close();

                        if ($ok) {
                            $display['status'] = 'success';
                            $display['message'] = 'Verifikasi berhasil! Akun Anda sudah aktif. Anda akan diarahkan ke halaman login.';
                            $autoRedirect = true;
                        } else {
                            $display['status'] = 'error';
                            $display['message'] = 'Gagal menyelesaikan verifikasi. Silakan coba lagi nanti.';
                            $display['show_resend_form'] = true;
                            $display['email'] = $email;
                        }
                    }
                }
            }
        }
    }
}

// ====== HTML OUTPUT ======
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verifikasi Email - Knowledge Battle</title>
<style>
:root{--bg:#f6f6f6;--card:#fff;--muted:#9aa4ad;--success:#059669;--error:#dc2626;--accent:#ef4444;}
body{margin:0;background:var(--bg);font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Arial;}
.container{max-width:720px;margin:40px auto;padding:16px;}
.card{background:var(--card);border-radius:12px;padding:28px;box-shadow:0 6px 20px rgba(0,0,0,0.08);text-align:center;}
h1{margin:0 0 12px;font-size:22px;color:#111827;}
.msg{padding:14px;border-radius:8px;margin-bottom:18px;font-weight:600;}
.msg.success{background:rgba(5,150,105,0.12);color:var(--success);border:1px solid rgba(5,150,105,0.14);}
.msg.error{background:rgba(220,38,38,0.06);color:var(--error);border:1px solid rgba(220,38,38,0.12);}
.msg.warning{background:rgba(245,158,11,0.06);color:#b45309;border:1px solid rgba(245,158,11,0.12);}
.actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:16px;}
.btn{display:inline-block;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:700;color:white;}
.btn.primary{background:var(--accent);}
.btn.secondary{background:#111827;}
.small{font-size:13px;color:var(--muted);margin-top:12px;}
.resend{margin-top:18px;text-align:center;}
.input, .input[type="email"]{padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;width:100%;max-width:360px;}
.form-inline{display:flex;gap:8px;justify-content:center;align-items:center;flex-wrap:wrap;margin-top:12px;}
.form-inline button{padding:10px 16px;border-radius:8px;border:none;background:var(--accent);color:white;font-weight:700;cursor:pointer;}
.footer{margin-top:20px;color:var(--muted);font-size:13px;}
a.link{color:var(--accent);text-decoration:none;font-weight:700;}
@media(max-width:480px){.card{padding:20px;}.input{max-width:100%;}}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h1>Verifikasi Email</h1>

    <?php
    $cls = 'error';
    if ($display['status'] === 'success') $cls = 'success';
    if ($display['status'] === 'warning') $cls = 'warning';
    ?>
    <?php if(!empty($display['message'])): ?>
      <div class="msg <?= $cls ?>"><?= $display['message'] ?></div>
    <?php endif; ?>

    <?php if(isset($autoRedirect) && $autoRedirect === true): ?>
      <p class="small">Anda akan diarahkan ke halaman login dalam 4 detik.</p>
      <p class="small"><a class="link" href="<?= APP_URL ?>/login_register/form_login.php">Klik di sini jika tidak diarahkan</a></p>
      <script>setTimeout(()=>{location.href='<?= APP_URL ?>/login_register/form_login.php'},4000);</script>
    <?php else: ?>
      <?php if($display['show_resend_form']): ?>
        <div class="resend">
          <p class="small">Belum menerima email verifikasi atau token kadaluarsa? Masukkan email Anda untuk mengirim ulang link verifikasi.</p>
          <form method="post" class="form-inline">
            <input name="email" type="email" class="input" placeholder="email@contoh.com" value="<?= htmlspecialchars($display['email'] ?? '', ENT_QUOTES) ?>" required>
            <input type="hidden" name="action" value="resend">
            <button type="submit">Kirim Ulang</button>
          </form>
        </div>
      <?php else: ?>
        <div class="actions">
          <a class="btn primary" href="<?= APP_URL ?>/login_register/form_login.php">Ke Halaman Login</a>
          <a class="btn secondary" href="<?= APP_URL ?>/">Kembali ke Home</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="footer">&copy; 2025 Knowledge Battle Forum</div>
  </div>
</div>
</body>
</html>
