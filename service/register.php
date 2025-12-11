<?php
// ============================================
// REGISTER LOGIC (process_register.php)
// ============================================
session_start();
include 'db.php';

$response = [
    'success' => false,
    'message' => '',
    'type' => 'error' // 'error', 'warning', 'success'
];

// Check if connection is established
if ($conn->connect_error) {
    $response['message'] = 'Koneksi database gagal. Silakan coba lagi.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_register.php");
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_register.php");
    exit;
}

// ============================================
// RATE LIMITING
// ============================================
if (!isset($_SESSION['register_attempts'])) {
    $_SESSION['register_attempts'] = [];
}

$current_time = time();
$rate_limit_window = 900; // 15 minutes
$max_attempts = 5;

// Clean old attempts
$_SESSION['register_attempts'] = array_filter(
    $_SESSION['register_attempts'],
    fn($time) => ($current_time - $time) < $rate_limit_window
);

// Add new attempt
$_SESSION['register_attempts'][] = $current_time;

if (count($_SESSION['register_attempts']) > $max_attempts) {
    $response['message'] = 'Terlalu banyak percobaan registrasi. Silakan coba lagi dalam 15 menit.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_register.php");
    exit;
}

// ============================================
// GET & VALIDATE INPUT
// ============================================
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

$errors = [];

// Email validation
if (empty($email)) {
    $errors[] = 'Email tidak boleh kosong';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
} elseif (strlen($email) > 255) {
    $errors[] = 'Email terlalu panjang (max 255 karakter)';
}

// Password validation
if (empty($password)) {
    $errors[] = 'Password tidak boleh kosong';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password minimal 8 karakter';
} elseif (strlen($password) > 255) {
    $errors[] = 'Password terlalu panjang';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password harus mengandung huruf besar (A-Z)';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors[] = 'Password harus mengandung huruf kecil (a-z)';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password harus mengandung angka (0-9)';
}

// Confirm password validation
if (empty($confirm_password)) {
    $errors[] = 'Konfirmasi password tidak boleh kosong';
} elseif ($password !== $confirm_password) {
    $errors[] = 'Password dan konfirmasi password tidak sama';
}

// Show validation errors
if (!empty($errors)) {
    $response['message'] = implode(' | ', $errors);
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_register.php");
    exit;
}

// ============================================
// CHECK EMAIL EXISTS
// ============================================
$check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$check_email) {
    $response['message'] = 'Error database. Silakan coba lagi.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_register.php");
    exit;
}

$check_email->bind_param("s", $email);
$check_email->execute();
$result = $check_email->get_result();

if ($result->num_rows > 0) {
    $response['message'] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
    $_SESSION['form_response'] = $response;
    $check_email->close();
    header("Location: ../View/login_register/form_register.php");
    exit;
}

$check_email->close();

// ============================================
// GENERATE USERNAME
// ============================================
$username = explode('@', $email)[0];
$original_username = $username;
$counter = 1;

while (true) {
    $check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$check_username) {
        $response['message'] = 'Error database. Silakan coba lagi.';
        $_SESSION['form_response'] = $response;
        header("Location: ../View/login_register/form_register.php");
        exit;
    }
    
    $check_username->bind_param("s", $username);
    $check_username->execute();
    
    if ($check_username->get_result()->num_rows === 0) {
        $check_username->close();
        break;
    }
    
    $check_username->close();
    $username = $original_username . $counter;
    $counter++;
    
    if ($counter > 100) {
        $response['message'] = 'Tidak bisa membuat username. Silakan coba dengan email lain.';
        $_SESSION['form_response'] = $response;
        header("Location: ../View/login_register/form_register.php");
        exit;
    }
}

// ============================================
// HASH PASSWORD & CREATE USER
// ============================================
$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$activation_token = bin2hex(random_bytes(32));
$token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Insert user
$insert_user = $conn->prepare("
    INSERT INTO users (email, username, password, level, title, created_at, is_verified)
    VALUES (?, ?, ?, 1, 'Member', NOW(), 0)
");

if (!$insert_user) {
    $response['message'] = 'Error database. Silakan coba lagi.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_register.php");
    exit;
}

$insert_user->bind_param("sss", $email, $username, $hashed_password);

if (!$insert_user->execute()) {
    $response['message'] = 'Registrasi gagal. Silakan coba lagi.';
    $_SESSION['form_response'] = $response;
    $insert_user->close();
    header("Location: ../View/login_register/form_register.php");
    exit;
}

$user_id = $conn->insert_id;
$insert_user->close();

// ============================================
// UPDATE ACTIVATION TOKEN (jika column ada)
// ============================================
$alter_check = $conn->query("DESCRIBE users activation_token");
if ($alter_check && $alter_check->num_rows > 0) {
    $update_token = $conn->prepare("
        UPDATE users 
        SET activation_token = ?, token_expiry = ? 
        WHERE id = ?
    ");
    if ($update_token) {
        $update_token->bind_param("ssi", $activation_token, $token_expiry, $user_id);
        $update_token->execute();
        $update_token->close();
    }
}
// ============================================
// SEND VERIFICATION EMAIL (via PHPMailer)
// ============================================
include 'email_helper.php';

$email_result = sendVerificationEmail($email, $username, $activation_token);

if (!$email_result['success']) {
    error_log("Failed to send verification email to: " . $email);
}
// ============================================
// SUCCESS - Simpan ke session untuk ditampilkan di halaman
// ============================================
$response['success'] = true;
$response['type'] = 'success';
$response['message'] = "Registrasi berhasil! Email verifikasi telah dikirim ke $email. Silakan cek email Anda (termasuk folder spam).";
$_SESSION['form_response'] = $response;

header("Location: ../View/login_register/form_register.php");
exit;
?>