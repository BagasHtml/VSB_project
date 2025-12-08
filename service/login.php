<?php
session_start();
include 'db.php';

// Rate limiting - prevent DDoS/brute force attacks
$ip = $_SERVER['REMOTE_ADDR'];
$rate_limit_key = "login_attempts_" . $ip;
$rate_limit_time = "login_attempts_time_" . $ip;

// Check rate limiting
if (isset($_SESSION[$rate_limit_key])) {
    if ($_SESSION[$rate_limit_key] >= 5) {
        // Check if 15 minutes have passed
        if (isset($_SESSION[$rate_limit_time])) {
            $time_diff = time() - $_SESSION[$rate_limit_time];
            if ($time_diff < 900) { // 15 minutes = 900 seconds
                header("Location: ../View/login_register/form_login.php?error=rate_limit");
                exit();
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

if(isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../View/login_register/form_login.php?error=invalid");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($user = $result->fetch_assoc()) {
        if(password_verify($password, $user['password'])) {
            // Reset rate limit on successful login
            $_SESSION[$rate_limit_key] = 0;
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Update last login time (optional)
            $update_login = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_login->bind_param("i", $user['id']);
            $update_login->execute();
            
            header("Location: ../View/halaman_utama.php");
            exit();
        } else {
            // Increment failed attempts
            $_SESSION[$rate_limit_key]++;
            header("Location: ../View/login_register/form_login.php?error=invalid");
            exit();
        }
    } else {
        // Increment failed attempts even if email doesn't exist (to prevent user enumeration)
        $_SESSION[$rate_limit_key]++;
        header("Location: ../View/login_register/form_login.php?error=invalid");
        exit();
    }
}
?>
