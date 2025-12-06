<?php 
$local = "localhost";
$user = "root";
$pass = "";
$dbs = "VSB";

$conn = mysqli_connect($local, $user, $pass, $dbs);

if ($conn->connect_error) {
    echo "Koneksi gagal";
    die();
}
?>