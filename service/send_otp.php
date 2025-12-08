<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

// Load .env file if it exists (for local development)
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

// Rate limiting - prevent DDoS
$ip = $_SERVER['REMOTE_ADDR'];
$rate_limit_key = "otp_request_" . $ip;
$rate_limit_time = "otp_request_time_" . $ip;

// Check if user has made too many requests
if (isset($_SESSION[$rate_limit_key])) {
    if ($_SESSION[$rate_limit_key] >= 3) {
        // Check if 15 minutes have passed
        if (isset($_SESSION[$rate_limit_time])) {
            $time_diff = time() - $_SESSION[$rate_limit_time];
            if ($time_diff < 900) { // 15 minutes = 900 seconds
                http_response_code(429);
                die(json_encode(['success' => false, 'message' => 'Terlalu banyak permintaan. Coba lagi nanti']));
            } else {
                // Reset after 15 minutes
                $_SESSION[$rate_limit_key] = 0;
                $_SESSION[$rate_limit_time] = time();
            }
        }
    }
} else {
    $_SESSION[$rate_limit_key] = 0;
    $_SESSION[$rate_limit_time] = time();
}

// Get email from POST
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Format email tidak valid']));
}

// Check if email already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Email sudah terdaftar']));
}

// Generate OTP (6 digits)
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otp_expiry = time() + 300; // OTP valid for 5 minutes

// Store OTP in session temporarily
$_SESSION['otp_' . $email] = $otp;
$_SESSION['otp_expiry_' . $email] = $otp_expiry;

// Try to send email using PHPMailer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    
    // Load email configuration
    $emailConfig = require 'config.email.php';
    $smtp = $emailConfig['smtp'];
    $from = $emailConfig['from'];
    
    // Check if credentials are configured (not placeholders)
    $isConfigured = ($smtp['username'] !== 'your-email@gmail.com' && 
                     $smtp['password'] !== 'your-app-password');
    
    if (!$isConfigured) {
        // Email not configured - return error
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Email belum dikonfigurasi. Hubungi administrator untuk setup email SMTP.'
        ]);
        exit;
    }
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = $smtp['auth'];
    $mail->Username = $smtp['username'];
    $mail->Password = $smtp['password'];
    $mail->SMTPSecure = ($smtp['secure'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp['port'];
    $mail->Timeout = 10;
    $mail->SMTPKeepAlive = true;

    // Recipients
    $mail->setFrom($from['email'], $from['name']);
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Kode OTP Verifikasi Email - Knowledge Battle';
    $mail->Body = "
    <html>
    <body style='font-family: Poppins, sans-serif; background: #f5f5f5; padding: 20px;'>
        <div style='max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <h2 style='color: #1a1a1a; text-align: center;'>Verifikasi Email Anda</h2>
            <p style='color: #666; text-align: center; margin: 10px 0;'>Gunakan kode OTP berikut untuk menyelesaikan pendaftaran:</p>
            
            <div style='background: #f5f5f5; padding: 20px; border-radius: 8px; text-align: center; margin: 25px 0;'>
                <h1 style='color: #FF2800; letter-spacing: 3px; margin: 0; font-size: 32px;'>" . implode(' ', str_split($otp)) . "</h1>
            </div>
            
            <p style='color: #999; text-align: center; font-size: 13px;'>Kode ini berlaku selama 5 menit</p>
            <p style='color: #999; text-align: center; font-size: 13px;'>Jika Anda tidak melakukan permintaan ini, abaikan email ini</p>
            
            <hr style='border: none; border-top: 1px solid #eee; margin: 25px 0;'>
            <p style='color: #999; text-align: center; font-size: 12px;'>
                © 2024 Knowledge Battle. All rights reserved.
            </p>
        </div>
    </body>
    </html>";

    $mail->send();
    
    // Increment rate limit counter
    $_SESSION[$rate_limit_key]++;
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'OTP telah dikirim ke email Anda']);
    
} catch (Exception $e) {
    // Email configuration error - provide helpful message
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim email: ' . $mail->ErrorInfo]);
}
?>
