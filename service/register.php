<?php
session_start();
include 'db.php';

require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Ganti dengan SMTP server Anda
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Ganti dengan email Anda
        $mail->Password = 'your-app-password'; // App Password dari Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Email settings
        $mail->setFrom('your-email@gmail.com', 'Knowledge Battle');
        $mail->addAddress($email, $username);
        $mail->Subject = 'Kode Verifikasi OTP - Knowledge Battle';
        $mail->isHTML(true);
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #ef4444;'>Knowledge Battle</h2>
            <p>Halo <strong>$username</strong>,</p>
            <p>Terima kasih telah mendaftar di Knowledge Battle! Gunakan kode OTP berikut untuk memverifikasi akun Anda:</p>
            <div style='background: #f3f4f6; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                $otp
            </div>
            <p style='color: #666;'>Kode ini akan kadaluarsa dalam 5 menit.</p>
            <p style='color: #666; font-size: 12px;'>Jika Anda tidak mendaftar, abaikan email ini.</p>
        </div>
        ";
        
        $mail->send();
        
        header("Location: ../View/login_register/verify_otp.php");
        exit();
        
    } catch (Exception $e) {
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