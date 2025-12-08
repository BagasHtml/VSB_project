<?php
session_start();
include '../../service/db.php';

if(!isset($_SESSION['register_data'])) {
    header("Location: form_register.php");
    exit();
}

$error = '';

if(isset($_POST['verify'])) {
    $input_otp = $_POST['otp'];
    $register_data = $_SESSION['register_data'];
    
    // Cek apakah OTP sudah kadaluarsa (5 menit)
    if(time() - $register_data['otp_time'] > 300) {
        $error = "Kode OTP sudah kadaluarsa. Silakan daftar ulang.";
    } elseif($input_otp == $register_data['otp']) {
        // OTP benar, buat akun
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $register_data['username'], $register_data['email'], $register_data['password']);
        
        if($stmt->execute()) {
            unset($_SESSION['register_data']);
            header("Location: form_login.php?registered=1");
            exit();
        } else {
            $error = "Gagal membuat akun: " . $conn->error;
        }
    } else {
        $error = "Kode OTP salah!";
    }
}

// Resend OTP
if(isset($_POST['resend'])) {
    $register_data = $_SESSION['register_data'];
    $new_otp = rand(100000, 999999);
    $_SESSION['register_data']['otp'] = $new_otp;
    $_SESSION['register_data']['otp_time'] = time();
    
    // Kirim ulang email OTP (gunakan kode yang sama seperti register.php)
    $error = "Kode OTP baru telah dikirim ke email Anda!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Knowledge Battle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .otp-input { width: 3rem; height: 3.5rem; font-size: 1.5rem; text-align: center; }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">

    <div class="glass rounded-3xl p-8 w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <i class="bi bi-envelope-check text-6xl text-white mb-4"></i>
            <h1 class="text-3xl font-bold text-white mb-2">Verifikasi Email</h1>
            <p class="text-white/80">Masukkan kode OTP yang telah dikirim ke</p>
            <p class="text-white font-semibold"><?= htmlspecialchars($_SESSION['register_data']['email']) ?></p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 rounded-xl p-3 mb-4 text-center text-white">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-6">
            <div>
                <label class="block text-white text-sm font-semibold mb-2 text-center">Kode OTP (6 Digit):</label>
                <input type="text" name="otp" required maxlength="6" pattern="[0-9]{6}"
                    class="w-full p-4 text-center text-2xl tracking-widest rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/60 focus:outline-none focus:border-white/40 transition"
                    placeholder="000000" autofocus>
            </div>

            <button type="submit" name="verify"
                class="w-full bg-white hover:bg-white/90 text-purple-600 font-bold py-3 rounded-xl transition transform hover:scale-105">
                Verifikasi
            </button>
        </form>

        <form method="post" action="" class="mt-4">
            <button type="submit" name="resend" class="w-full text-white/80 hover:text-white text-sm">
                Tidak menerima kode? <span class="font-semibold">Kirim Ulang</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="form_register.php" class="text-white/60 hover:text-white text-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Register
            </a>
        </div>
    </div>

</body>
</html>