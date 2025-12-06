<?php 
include 'db.php';

$email = $_POST['email'];
$user = $_POST['username'];
$pass = $_POST['password'];

$sql = "SELECT * FROM login WHERE email = '$email' and username = '$user' and password = '$pass'";
$hasil = mysqli_query($conn, $sql);

if(mysqli_num_rows($hasil) > 0) {
    header("location: View/halaman_utama.php");
} else {
    echo "Login gagal";
}
?>