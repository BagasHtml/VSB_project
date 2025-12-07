<?php 
include 'db.php';

$email = $_POST['email'];
$user = $_POST['username'];
$pass = $_POST['password'];

$p = "";
echo $p;

$sql = "SELECT * FROM login WHERE email = '$email' and username = '$user' and password = '$pass'";
$hasil = mysqli_query($conn, $sql);

try {
    if(mysqli_num_rows($hasil) > 0) {
    header("location: ../View/halaman_utama.php"); 
} else {
    echo "Login gagal";
};
} catch (Exception $p) {
    echo "Error, akun sudah terdaftar" . $p->getMessage() . "";
}
?>