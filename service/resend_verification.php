<?php
// ============================================
// RESEND VERIFICATION EMAIL (resend_verification.php)
// ============================================
session_start();
include 'db.php';

$response = [
    'success' => false,
    'message' => '',
    'type' => 'error'
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

// ============================================
// RATE LIMITING
// ============================================
$ip = $_SERVER['REMOTE_ADDR'];
$resend_key = "resend_attempts_" . $ip;
$resend_time_key = "resend_attempts_time_" . $ip;

if (!isset($_SESSION[$resend_key])) {
    $_SESSION[$resend_key] = 0;
    $_SESSION[$resend_time_key] = time();
}

$current_time = time();
$rate_limit_window = 3600; // 1 hour
$max_attempts = 3;

// Reset counter jika window sudah lewat
if ($current_time - $_SESSION[$resend_time_key] >= $rate_limit_window) {
    $_SESSION[$resend_key] = 0;
    $_SESSION[$resend_time_key] = $current_time;
}

// Check if rate limit exceeded
// if ($_SESSION[$resend_key] >= $max_attempts) {
//     $response['message'] = 'Terlalu banyak permintaan resend. Silakan coba lagi dalam 1 jam.';
//     $response['type'] = 'warning';
//     $_SESSION['form_response'] = $response;
//     header("Location: ../View/login_register/form_login.php");
//     exit;
// }

// ============================================
// GET & VALIDATE INPUT
// ============================================
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    $response['message'] = 'Email tidak boleh kosong.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Format email tidak valid.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

// ============================================
// CHECK USER
// ============================================
$stmt = $conn->prepare("
    SELECT id, username, is_verified 
    FROM users 
    WHERE email = ?
");

if (!$stmt) {
    $response['message'] = 'Error database. Silakan coba lagi.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// User not found - don't reveal if email exists (security)
if (!$user) {
    $response['success'] = true;
    $response['type'] = 'success';
    $response['message'] = 'Jika email terdaftar, link verifikasi akan dikirim. Silakan cek inbox Anda.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

// Already verified
if ($user['is_verified']) {
    $response['success'] = true;
    $response['type'] = 'success';
    $response['message'] = 'Akun Anda sudah terverifikasi. Silakan login.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

// ============================================
// GENERATE NEW TOKEN & UPDATE DATABASE
// ============================================
$activation_token = bin2hex(random_bytes(32));
$token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Update token
$update = $conn->prepare("
    UPDATE users 
    SET activation_token = ?, token_expiry = ?
    WHERE id = ?
");

if (!$update) {
    $response['message'] = 'Error database. Silakan coba lagi.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

$update->bind_param("ssi", $activation_token, $token_expiry, $user['id']);

if (!$update->execute()) {
    $response['message'] = 'Gagal membuat token baru. Silakan coba lagi.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}

$update->close();

// ============================================
// SEND EMAIL (via PHPMailer)
// ============================================
require __DIR__ . '/email_helper.php';

$email_result = sendResendVerificationEmail($email, $user['username'], $activation_token);

if (!$email_result['success']) {
    error_log("Failed to send resend verification email to: " . $email);
}

// ============================================
// INCREMENT RESEND ATTEMPTS
// ============================================
$_SESSION[$resend_key]++;
$_SESSION[$resend_time_key] = $current_time;

$conn->close();

// ============================================
// SUCCESS RESPONSE
// ============================================
$response['success'] = true;
$response['type'] = 'success';
$response['message'] = 'Email verifikasi telah dikirim. Silakan cek inbox Anda (termasuk folder spam).';
$_SESSION['form_response'] = $response;

header("Location: ../View/login_register/form_login.php");
exit;
?>