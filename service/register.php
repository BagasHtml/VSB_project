<?php
session_start();
include 'db.php';

require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if(isset($_POST['username'], $_POST['email'], $_POST['password'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if($check->get_result()->num_rows > 0) {
        header("Location: ../View/login_register/form_register.php?error=" . urlencode("Email sudah terdaftar!"));
        exit();
    }
    
    $otp = rand(100000, 999999);
    $_SESSION['register_data'] = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'otp' => $otp,
        'otp_time' => time()
    ];
    
    $mail = new PHPMailer(true);
    
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Ganti dengan email Anda
        $mail->Password = 'your-app-password'; // Ganti dengan App Password dari Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Debug mode (matikan di production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Email settings
        $mail->setFrom('your-email@gmail.com', 'Knowledge Battle');
        $mail->addAddress($email, $username);
        $mail->Subject = 'Kode Verifikasi OTP - Knowledge Battle';
        $mail->isHTML(true);
        
        // Template email HTML
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; border-radius: 10px;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h1 style='color: #ef4444; margin: 0;'>Knowledge Battle</h1>
                <p style='color: #666; margin: 5px 0;'>Forum Diskusi Komunitas</p>
            </div>
            
            <div style='background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <p style='font-size: 16px; color: #333;'>Halo <strong>$username</strong>,</p>
                <p style='font-size: 16px; color: #333;'>Terima kasih telah mendaftar di Knowledge Battle! Gunakan kode OTP berikut untuk memverifikasi akun Anda:</p>
                
                <div style='background: linear-gradient(135deg, #ef4444, #dc2626); padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 8px; color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.3);'>
                    $otp
                </div>
                
                <p style='color: #666; font-size: 14px;'>Kode ini akan kadaluarsa dalam <strong>5 menit</strong>.</p>
                <p style='color: #666; font-size: 14px;'>Jika Anda tidak mendaftar, abaikan email ini.</p>
            </div>
            
            <div style='text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;'>
                <p>&copy; " . date('Y') . " Knowledge Battle. All rights reserved.</p>
            </div>
        </div>
        ";
        
        // Versi teks untuk email client yang tidak mendukung HTML
        $mail->AltBody = "Halo $username,\n\nTerima kasih telah mendaftar di Knowledge Battle! Gunakan kode OTP berikut untuk memverifikasi akun Anda:\n\n$otp\n\nKode ini akan kadaluarsa dalam 5 menit.\n\nJika Anda tidak mendaftar, abaikan email ini.";
        
        $mail->send();
        
        // Redirect ke halaman verifikasi OTP
        header("Location: ../View/login_register/verify_otp.php");
        exit();
        
    } catch (Exception $e) {
        // Log error untuk debugging
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        
        // Jika gagal mengirim email, simpan langsung ke database (opsional)
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        
        if($stmt->execute()) {
            header("Location: ../View/login_register/form_login.php?registered=1");
            exit();
        } else {
            header("Location: ../View/login_register/form_register.php?error=" . urlencode("Gagal mendaftar: " . $conn->error));
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Knowledge Battle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        <div class="glass rounded-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">
                    <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
                </h1>
                <p class="text-gray-400">Buat Akun Baru</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            
            <form action="../../Controller/register.php" method="post" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-person text-gray-400"></i>
                        </div>
                        <input type="text" id="username" name="username" required
                               class="w-full pl-10 pr-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500 text-white"
                               placeholder="Masukkan username">
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-envelope text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" required
                               class="w-full pl-10 pr-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500 text-white"
                               placeholder="Masukkan email">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required minlength="6"
                               class="w-full pl-10 pr-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500 text-white"
                               placeholder="Minimal 6 karakter">
                    </div>
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium py-3 px-4 rounded-lg transition">
                        Daftar
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-6">
                <p class="text-gray-400">Sudah punya akun? <a href="form_login.php" class="text-red-400 hover:text-red-300">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>