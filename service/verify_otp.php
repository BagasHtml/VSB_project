<?php
session_start();
include 'db.php';
require __DIR__ . '/../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

// Cek apakah data registrasi ada di session
if (!isset($_SESSION['register_data'])) {
    header("Location: ../View/login_register/form_register.php");
    exit();
}

// Cek apakah OTP kadaluarsa (5 menit)
if (time() - $_SESSION['register_data']['otp_time'] > 300) {
    unset($_SESSION['register_data']);
    header("Location: ../View/login_register/form_register.php?error=" . urlencode("Kode OTP telah kadaluarsa! Silakan daftar kembali."));
    exit();
}

 $error = '';
 $success = '';

// Proses verifikasi OTP
if (isset($_POST['otp']) && !isset($_POST['resend'])) {
    $input_otp = $_POST['otp'];
    
    if ($input_otp == $_SESSION['register_data']['otp']) {
        // OTP benar, simpan data ke database
        $data = $_SESSION['register_data'];
        
        // Tambahkan kolom default untuk user baru
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, title, level, created_at) VALUES (?, ?, ?, 'Member', 1, NOW())");
        $stmt->bind_param("sss", $data['username'], $data['email'], $data['password']);
        
        if ($stmt->execute()) {
            // Hapus session setelah berhasil
            unset($_SESSION['register_data']);
            
            header("Location: ../View/login_register/form_login.php?success=" . urlencode("Registrasi berhasil! Silakan login."));
            exit();
        } else {
            $error = "Gagal menyimpan data: " . $conn->error;
        }
    } else {
        $error = "Kode OTP salah!";
    }
}

// Kirim ulang OTP
if (isset($_POST['resend'])) {
    $otp = rand(100000, 999999);
    $_SESSION['register_data']['otp'] = $otp;
    $_SESSION['register_data']['otp_time'] = time();
    
    $mail = new PHPMailer(true);
    
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bagashtml369@gmail.com'; // Ganti dengan email Anda
        $mail->Password = 'gmvs aymb xidc mxnt'; // Ganti dengan App Password dari Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Aktifkan debug jika diperlukan (comment di production)
        // $mail->SMTPDebug = SMTP::DEBUG_OFF;
        
        $mail->setFrom('bagashtml369@gmail.com', 'Knowledge Battle');
        $mail->addAddress($_SESSION['register_data']['email'], $_SESSION['register_data']['username']);
        $mail->Subject = 'Kode Verifikasi OTP Baru - Knowledge Battle';
        $mail->isHTML(true);
        
        // Template email HTML
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; border-radius: 10px;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h1 style='color: #ef4444; margin: 0;'>Knowledge Battle</h1>
                <p style='color: #666; margin: 5px 0;'>Forum Diskusi Komunitas</p>
            </div>
            
            <div style='background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <p style='font-size: 16px; color: #333;'>Halo <strong>{$_SESSION['register_data']['username']}</strong>,</p>
                <p style='font-size: 16px; color: #333;'>Berikut adalah kode OTP baru Anda:</p>
                
                <div style='background: linear-gradient(135deg, #ef4444, #dc2626); padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 8px; color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.3);'>
                    $otp
                </div>
                
                <p style='color: #666; font-size: 14px;'>Kode ini akan kadaluarsa dalam <strong>5 menit</strong>.</p>
                <p style='color: #666; font-size: 14px;'>Jika Anda tidak meminta kode ini, abaikan email ini.</p>
            </div>
            
            <div style='text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;'>
                <p>&copy; " . date('Y') . " Knowledge Battle. All rights reserved.</p>
            </div>
        </div>
        ";
        
        // Versi teks untuk email client yang tidak mendukung HTML
        $mail->AltBody = "Halo {$_SESSION['register_data']['username']},\n\nBerikut adalah kode OTP baru Anda:\n\n$otp\n\nKode ini akan kadaluarsa dalam 5 menit.\n\nJika Anda tidak meminta kode ini, abaikan email ini.";
        
        $mail->send();
        $success = "Kode OTP baru telah dikirim ke email Anda!";
    } catch (Exception $e) {
        // Log error untuk debugging
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        $error = "Gagal mengirim email: " . $mail->ErrorInfo;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Knowledge Battle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .otp-input { letter-spacing: 0.5em; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        <div class="glass rounded-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">
                    <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
                </h1>
                <p class="text-gray-400">Verifikasi Akun Anda</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-6">
                <p class="text-gray-300 mb-4">Kami telah mengirim kode OTP ke email <strong><?= htmlspecialchars($_SESSION['register_data']['email']) ?></strong>. Masukkan kode tersebut di bawah ini:</p>
                
                <form method="post" class="space-y-4">
                    <div>
                        <label for="otp" class="block text-sm font-medium text-gray-300 mb-2">Kode OTP</label>
                        <div class="relative">
                            <input type="text" id="otp" name="otp" required maxlength="6" pattern="[0-9]{6}" 
                                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500 text-white text-center text-2xl tracking-widest otp-input"
                                   placeholder="000000">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="bi bi-shield-lock text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium py-3 px-4 rounded-lg transition">
                        Verifikasi
                    </button>
                </form>
            </div>
            
            <div class="text-center">
                <p class="text-gray-400 mb-2">Tidak menerima kode?</p>
                <form method="post">
                    <button type="submit" name="resend" class="text-red-400 hover:text-red-300 text-sm underline">
                        Kirim Ulang Kode OTP
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus pada input OTP
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            otpInput.focus();
            
            // Format input OTP
            otpInput.addEventListener('input', function(e) {
                // Hanya izinkan angka
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto-submit ketika 6 digit dimasukkan
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
        });
    </script>
</body>
</html>