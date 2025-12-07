<?php 
include 'db.php';

$email = $_POST['email'];
$user = $_POST['username'];
$pass = $_POST['password'];

$sql = "INSERT INTO login (email, username, password) VALUES ('$email', '$user', '$pass')";
$hasil = mysqli_query($conn, $sql);

if($hasil) {
    header("location: ../View/login_register/form_login.php"); 
} else {
    echo "Login gagal";
}
?>