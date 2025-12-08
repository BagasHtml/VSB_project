<?php
session_start();
include 'db.php';

// Check if email is verified
if (!isset($_SESSION['verified_email'])) {
    header("Location: ../View/login_register/form_register.php?error=email_not_verified");
    exit();
}

$verified_email = $_SESSION['verified_email'];
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

// Validate inputs
if (empty($username) || empty($password) || empty($password_confirm)) {
    header("Location: ../View/login_register/form_register.php?error=empty_fields");
    exit();
}

// Validate username (alphanumeric, underscores, 3-20 characters)
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    header("Location: ../View/login_register/form_register.php?error=invalid_username");
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    header("Location: ../View/login_register/form_register.php?error=password_too_short");
    exit();
}

// Check if passwords match
if ($password !== $password_confirm) {
    header("Location: ../View/login_register/form_register.php?error=password_mismatch");
    exit();
}

// Check if email already exists (double-check)
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $verified_email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: ../View/login_register/form_register.php?error=email_exists");
    exit();
}

// Check if username already exists
$check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check_username->bind_param("s", $username);
$check_username->execute();
$result_username = $check_username->get_result();

if ($result_username->num_rows > 0) {
    header("Location: ../View/login_register/form_register.php?error=username_exists");
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert user into database
$insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role, level, title, created_at) VALUES (?, ?, ?, 'user', 0, '', NOW())");
$insert_stmt->bind_param("sss", $username, $verified_email, $hashed_password);

if ($insert_stmt->execute()) {
    // Clear session data
    unset($_SESSION['verified_email']);
    
    // Set success message and redirect to login
    header("Location: ../View/login_register/form_login.php?success=registration_complete");
    exit();
} else {
    header("Location: ../View/login_register/form_register.php?error=registration_failed");
    exit();
}
?>
