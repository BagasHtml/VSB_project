<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['id'] ?? 0;

// Cek apakah post milik user ini
$stmt = $conn->prepare("SELECT user_id, image FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if($post && $post['user_id'] == $user_id) {
    // Hapus gambar jika ada
    if($post['image'] && file_exists("../uploads/" . $post['image'])) {
        unlink("../uploads/" . $post['image']);
    }
    
    // Hapus post (foreign key akan auto hapus comments, likes, notifications)
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    
    header("Location: forum.php?deleted=1");
} else {
    header("Location: forum.php?error=unauthorized");
}
exit;
?>