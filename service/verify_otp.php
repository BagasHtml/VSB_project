<?php
header('Content-Type: application/json');
session_start();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? trim($input['email']) : '';
$otp = isset($input['otp']) ? trim($input['otp']) : '';

// Validate inputs
if (empty($email) || empty($otp)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Email dan OTP harus diisi']));
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Format email tidak valid']));
}

// Check OTP in session
$otp_key = 'otp_' . $email;
$otp_expiry_key = 'otp_expiry_' . $email;

if (!isset($_SESSION[$otp_key]) || !isset($_SESSION[$otp_expiry_key])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'OTP tidak ditemukan. Minta kode baru']));
}

// Check if OTP has expired
if (time() > $_SESSION[$otp_expiry_key]) {
    unset($_SESSION[$otp_key]);
    unset($_SESSION[$otp_expiry_key]);
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'OTP telah kadaluarsa. Minta kode baru']));
}

// Verify OTP
if ($_SESSION[$otp_key] !== $otp) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Kode OTP salah']));
}

// OTP is valid, mark email as verified
$_SESSION['verified_email'] = $email;
unset($_SESSION[$otp_key]);
unset($_SESSION[$otp_expiry_key]);

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Email berhasil diverifikasi']);
?>
