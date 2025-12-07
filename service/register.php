<?php
session_start();
include 'db.php'; // path sesuai lokasi

if(isset($_POST['username'], $_POST['email'], $_POST['password'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if($stmt->execute()) {
        header("Location: ../View/login_register/form_login.php");
        exit();
    } else {
        echo "Gagal daftar: ".$conn->error;
    }
}
