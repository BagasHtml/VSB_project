<?php
session_start();
require_once '../../service/db.php';

// Cek otorisasi admin
if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')
) {
    header("Location: admin_login.php");
    exit();
}

// Cek apakah ID user dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID User tidak valid.";
    header("Location: index.php");
    exit();
}

 $userIdToDelete = $_GET['id'];
 $currentAdminId = $_SESSION['admin_id'];

// Cegah admin menghapus dirinya sendiri
if ($userIdToDelete == $currentAdminId) {
    $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    header("Location: index.php");
    exit();
}

// Hapus terkait user dari tabel lain terlebih dahulu (untuk menjaga integritas data)
// Hapus komentar yang dibuat user
 $conn->query("DELETE FROM comments WHERE user_id = $userIdToDelete");

// Hapus like yang diberikan user
 $conn->query("DELETE FROM post_likes WHERE user_id = $userIdToDelete");

// Hapus post yang dibuat user
 $conn->query("DELETE FROM posts WHERE user_id = $userIdToDelete");

// Sekarang, hapus user itu sendiri
 $sql = "DELETE FROM users WHERE id = ?";
 $stmt = $conn->prepare($sql);
 $stmt->bind_param("i", $userIdToDelete);

if ($stmt->execute()) {
    $_SESSION['success'] = "User berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus user: " . $conn->error;
}

 $stmt->close();
 $conn->close();

header("Location: admin_panel.php");
exit();