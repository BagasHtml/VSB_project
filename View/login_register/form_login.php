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
        $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
        $success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
        
        if($success): ?>
        <div class="success-message">
            <i class="bi bi-check-circle-fill"></i>
            <?php
            switch($success) {
                case 'registration_complete': echo 'Pendaftaran berhasil! Silakan masuk dengan akun Anda'; break;
                default: echo htmlspecialchars($success);
            }
            ?>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="error-message">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?php
            switch($error) {
                case 'invalid': echo 'Email atau password tidak valid'; break;
                case 'not_found': echo 'Akun tidak ditemukan'; break;
                case 'rate_limit': echo 'Terlalu banyak percobaan. Coba lagi dalam 15 menit'; break;
                default: echo htmlspecialchars($error);
            }
            ?>
        </div>
        <?php endif; ?>

        <form action="../../service/login.php" method="post">
            <div class="input-box">
                <input type="email" name="email" required autocomplete="email" placeholder="nama@example.com">
                <label><i class="bi bi-envelope"></i> Alamat Email</label>
            </div>

            <div class="input-box">
                <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                <label><i class="bi bi-lock"></i> Kata Sandi</label>
            </div>

            <button class="btn" type="submit">
                <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
            </button>

            <p class="switch">
                Belum punya akun?
                <a href="form_register.php">Daftar di sini</a>
            </p>
        </form>
    </div>
</div>

<script src="/Design/script.js"></script>
</body>
</html>
