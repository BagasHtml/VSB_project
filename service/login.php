<?php
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



// ========================== RATE LIMITING ==============================
$ip = $_SERVER['REMOTE_ADDR'];
$rate_key = "login_attempts_" . $ip;
$time_key = "login_attempts_time_" . $ip;

if (!isset($_SESSION[$rate_key])) {
    $_SESSION[$rate_key] = 0;
    $_SESSION[$time_key] = time();
}

$current_time = time();
$rate_limit_window = 900;
$max_attempts = 5;

if ($current_time - $_SESSION[$time_key] >= $rate_limit_window) {
    $_SESSION[$rate_key] = 0;
    $_SESSION[$time_key] = $current_time;
}

// if ($_SESSION[$rate_key] >= $max_attempts) {
//     $response['type'] = 'warning';
//     $response['message'] = 'Terlalu banyak percobaan login gagal. Coba lagi 15 menit.';
//     $_SESSION['form_response'] = $response;
//     header("Location: ../View/login_register/form_login.php");
//     exit;
// }



// ========================== VALIDASI INPUT ==============================
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
if ($password === '') $errors[] = 'Password tidak boleh kosong.';

if (!empty($errors)) {
    $response['message'] = implode(' | ', $errors);
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}



// ========================== CEK USER ==============================
$stmt = $conn->prepare("SELECT id, username, password, email_verified, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION[$rate_key]++;
    $_SESSION[$time_key] = $current_time;
    $response['message'] = 'Email atau password salah.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}



// ========================== CEK VERIFIKASI EMAIL ==============================
if ($user['email_verified'] != 1) {   // DI SINI PERUBAHANNYA!
    $_SESSION['last_login_email'] = $email;
    $response['type'] = 'warning';
    $response['message'] = 'Akun belum diverifikasi. Silakan cek email Anda.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}



// ========================== CEK PASSWORD ==============================
if (!password_verify($password, $user['password'])) {
    $_SESSION[$rate_key]++;
    $_SESSION[$time_key] = $current_time;
    $response['message'] = 'Email atau password salah.';
    $_SESSION['form_response'] = $response;
    header("Location: ../View/login_register/form_login.php");
    exit;
}



// ========================== LOGIN BERHASIL ==============================
$_SESSION[$rate_key] = 0;
unset($_SESSION[$time_key]);

$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

$update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$update->bind_param("i", $user['id']);
$update->execute();
$update->close();

$conn->close();

unset($_SESSION['form_response']);

header("Location: ../View/halaman_utama.php");
exit;
?>
