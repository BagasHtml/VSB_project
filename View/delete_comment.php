<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

 $comment_id = $_POST['comment_id'];
 $current_user_id = $_SESSION['user_id'];
 $current_user_role = $_SESSION['role'] ?? 'member';

// Ambil detail komentar untuk pengecekan permission
 $commentStmt = $conn->prepare("
    SELECT c.user_id, c.post_id, p.user_id as post_author_id 
    FROM comments c
    JOIN posts p ON c.post_id = p.id
    WHERE c.id = ?
");
 $commentStmt->bind_param("i", $comment_id);
 $commentStmt->execute();
 $comment_details = $commentStmt->get_result()->fetch_assoc();

if (!$comment_details) {
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit();
}

 $can_delete = (
    $current_user_id == $comment_details['user_id'] || // Pemilik komentar
    $current_user_id == $comment_details['post_author_id'] || // Pemilik post
    in_array($current_user_role, ['admin', 'developer']) // Admin/Dev
);

if ($can_delete) {
    // Hapus komentar (dan balasannya jika ada, jika database mendukung ON DELETE CASCADE)
    $deleteStmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $deleteStmt->bind_param("i", $comment_id);
    
    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete comment from database.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment.']);
}
?>