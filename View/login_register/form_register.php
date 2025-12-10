<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Knowledge Battle</title>
    <link rel="stylesheet" href="../../Design/Css/auth.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<div class="wrapper">
    <div class="form-box">
        <h2><i class="bi bi-shield-check"></i> Daftar</h2>
        <p class="form-subtitle">Daftar dulu yuk!</p>

        <?php
        session_start();
        
        // Ambil pesan dari session
        $form_response = isset($_SESSION['form_response']) ? $_SESSION['form_response'] : null;
        unset($_SESSION['form_response']);
        
        // Tampilkan pesan jika ada
        if($form_response):
            $type = htmlspecialchars($form_response['type']);
            $message = htmlspecialchars($form_response['message']);
            $icon = ($type === 'success') ? 'check-circle-fill' : (($type === 'warning') ? 'exclamation-triangle-fill' : 'exclamation-circle-fill');
            $class = 'alert alert-' . $type;
        ?>
        <div class="<?php echo $class; ?>" role="alert">
            <i class="bi bi-<?php echo $icon; ?>"></i>
            <div style="flex: 1;">
                <?php echo $message; ?>
                <?php if($type === 'success'): ?>
                    <div style="margin-top: 10px; font-size: 13px;">
                        <p style="margin: 5px 0;">📧 Email verifikasi telah dikirim!</p>
                        <p style="margin: 5px 0;">Cek folder spam jika tidak menemukan email.</p>
                        <p style="margin: 5px 0;">Setelah verifikasi, Anda bisa login.</p>
                    </div>
                    <a href="form_login.php" style="display: inline-block; margin-top: 10px; color: #15803d; text-decoration: underline; font-weight: 600;">
                        → Ke Halaman Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form daftar (sembunyikan jika registrasi sukses) -->
        <?php if(!$form_response || $form_response['type'] !== 'success'): ?>
        <form action="../../service/register.php" method="post">
            <div class="input-box">
                <input type="email" name="email" required autocomplete="email" placeholder="nama@example.com">
                <label><i class="bi bi-envelope"></i> Alamat Email</label>
            </div>

            <div class="input-box">
                <input type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                <label><i class="bi bi-lock"></i> Kata Sandi</label>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Min 8 karakter, huruf besar, huruf kecil, dan angka
                </small>
            </div>

            <div class="input-box">
                <input type="password" name="confirm_password" required autocomplete="new-password" placeholder="••••••••">
                <label><i class="bi bi-lock"></i> Konfirmasi Kata Sandi</label>
            </div>

            <button class="btn" type="submit">
                <i class="bi bi-person-plus"></i> Daftar Sekarang
            </button>

            <div class="social-divider">
                <span>atau</span>
            </div>
            <div class="social-buttons">
                <a href="../../service/oauth/google_login.php" class="social-btn google" title="Login dengan Google">
                    <i class="bi bi-google"></i> Google
                </a>
                <a href="../../service/oauth/facebook_login.php" class="social-btn facebook" title="Login dengan Facebook">
                    <i class="bi bi-facebook"></i> Facebook
                </a>
            </div>

            <p class="switch">
                Sudah punya akun?
                <a href="form_login.php">Login di sini</a>
            </p>
        </form>
        <?php endif; ?>

        <!-- Form resend verifikasi (tampilkan jika belum sukses) -->
        <?php if($form_response && $form_response['type'] === 'error' && strpos($form_response['message'], 'belum diverifikasi') !== false): ?>
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <h3 style="text-align: center; color: #1f2937; margin-bottom: 15px; font-size: 16px;">
                <i class="bi bi-envelope-check"></i> Kirim Ulang Email Verifikasi
            </h3>
            <form action="../../service/register.php" method="post">
                <div class="input-box">
                    <input type="email" name="email" required placeholder="nama@example.com">
                    <label><i class="bi bi-envelope"></i> Alamat Email</label>
                </div>
                <button class="btn" type="submit" style="margin-bottom: 10px;">
                    <i class="bi bi-send"></i> Kirim Ulang Verifikasi
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .alert {
        margin-bottom: 20px;
        border-radius: 8px;
        padding: 15px 20px;
        animation: slideDown 0.3s ease-out;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-error {
        background-color: #fee;
        border-left: 4px solid #dc2626;
        color: #991b1b;
    }

    .alert-warning {
        background-color: #fef3c7;
        border-left: 4px solid #f59e0b;
        color: #78350f;
    }

    .alert-success {
        background-color: #dcfce7;
        border-left: 4px solid #16a34a;
        color: #15803d;
    }

    .alert i {
        font-weight: bold;
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .input-box small {
        font-size: 12px;
    }
</style>

<script src="/Design/script.js"></script>
</body>
</html>