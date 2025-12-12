<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Knowledge Battle</title>
    <link rel="stylesheet" href="../../Design/Css/auth.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<div class="wrapper">
    <div class="form-box">
        <h2><i class="bi bi-shield-check"></i> Masuk</h2>
        <p class="form-subtitle">Selamat kembali ke Knowledge Battle</p>

        <?php
        session_start();
        
        // Ambil pesan dari session
        $form_response = isset($_SESSION['form_response']) ? $_SESSION['form_response'] : null;
        unset($_SESSION['form_response']);
        
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
                
                <!-- Jika belum verifikasi, tawarkan untuk kirim ulang -->
                <?php 
                if($type === 'warning' && strpos($message, 'belum diverifikasi') !== false):
                    // Ambil email dari form login (yang baru saja di-submit)
                    $last_email = isset($_SESSION['last_login_email']) ? $_SESSION['last_login_email'] : '';
                ?>
                    <div style="margin-top: 10px;">
                        <form action="../../service/resend_verification.php" method="post" style="display: flex; gap: 10px;">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($last_email); ?>">
                            <button type="submit" style="background: none; border: none; color: #0891b2; text-decoration: underline; cursor: pointer; font-weight: 600; padding: 0;">
                                Kirim Ulang Email Verifikasi
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <form action="../../service/login.php" method="post">
            <div class="input-box">
                <input type="email" name="email" required autocomplete="email" placeholder="nama@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <label><i class="bi bi-envelope"></i> Alamat Email</label>
            </div>

            <div class="input-box">
                <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                <label><i class="bi bi-lock"></i> Kata Sandi</label>
            </div>

            <button class="btn" type="submit">
                <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
            </button>

            <div class="social-divider">
                <span>atau</span>
            </div>
            <div class="social-buttons">
                <a href="../../service/oauth/google_login.php" class="social-btn google" title="Login dengan Google">
                    <i class="bi bi-google"></i> Google
                </a>
                <a href="../../service/oauth/discord_login.php" class="social-btn discord" title="Login dengan Discord">
                    <i class="bi bi-discord"></i> Discord
                </a>
            </div>

            <p class="switch">
                Belum punya akun?
                <a href="form_register.php">Daftar di sini</a>
            </p>

            <p class="switch">
                Lupa kata sandi?
                <a href="https://wa.me/6281321720348?text=Permisi min, saya lupa password akun, bisa di bantu min? terimakasih.">Lapor Admin</a>
        </form>
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
</style>

<script src="/Design/script.js"></script>
</body>
</html>