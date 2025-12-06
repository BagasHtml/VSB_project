<?php
include 'db.php';

$email = trim($_POST['email']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (empty($email) || empty($username) || empty($password)) {
    echo "Semua field wajib diisi!";
    exit;
}

$check = $conn->prepare("SELECT id FROM login WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Email sudah terdaftar!";
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = $conn->prepare("INSERT INTO login (email, username, password) VALUES (?, ?, ?)");
$sql->bind_param("sss", $email, $username, $hashedPassword);

if ($sql->execute()) {
    header("Location: ../View/login_register/form_login.php");
    exit;
} else {
    echo "Gagal mendaftar. Error: " . $conn->error;
}
?>
