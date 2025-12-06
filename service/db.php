<?php 
$local = "localhost";
$user = "root";
$pass = "";
$dbs = "VSB";

$db = mysqli_connect($local, $user, $pass, $db);

if ($db->connect_error) {
    echo "Koneksi gagal";
    die();
}
?>