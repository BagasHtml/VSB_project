<?php 
include '../VSB_project/service/db.php'; 

$email = trim($_POST['email']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (empty($email) || empty($username) || empty($password)) {
    echo "Semua field wajib diisi!";
    exit;
}

$sql = "SELECT * FROM login WHERE email = ? AND username = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row['password'])) {

        session_start();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];

        header("Location: /View/halaman_utama.php");
        exit;
    } else {
        echo "Password salah!";
    }
} else {
    echo "Akun tidak ditemukan!";
}
?>
