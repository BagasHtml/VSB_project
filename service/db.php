<?php
$local = "127.0.0.1";
$user  = "root";
$pass  = "";
$dbs   = "vsb";

$conn = mysqli_connect($local, $user, $pass, $dbs);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
