<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php';

if(isset($_POST['email'], $_POST['password'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {

        if (password_verify($password, $user['password'])) {

            if ($user['role'] === 'admin' || $user['role'] === 'developer') {

                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                header("Location: ../View/admin/admin_panel.php");
                exit();

            } else {
                header("Location: ../View/admin/admin_login.php?error=" . urlencode("Akses ditolak! Kamu bukan admin."));
                exit();
            }

        } else {
            header("Location: ../View/admin/admin_login.php?error=" . urlencode("Password salah."));
            exit();
        }

    } else {
        header("Location: ../View/admin/admin_login.php?error=" . urlencode("Email tidak ditemukan."));
        exit();
    }
}
