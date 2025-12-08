<?php
session_start();
include '../service/db.php';

if(!isset($_GET['id'])){ 
    header("Location: halaman_utama.php");
    exit;
}

$post_id = $_GET['id'];

$conn->query("DELETE FROM comments WHERE parent_id IN (SELECT id FROM comments WHERE post_id=$post_id)");

$conn->query("DELETE FROM comments WHERE post_id=$post_id");

$conn->query("DELETE FROM post_likes WHERE post_id=$post_id");

$conn->query("DELETE FROM notifications WHERE post_id=$post_id");

/* 5. Baru hapus postingan */
$stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
$stmt->bind_param("i",$post_id);
$stmt->execute();

header("Location: halaman_utama.php");
exit;
?>
