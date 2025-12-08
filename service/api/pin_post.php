<?php
session_start();
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

// Cek login
if(!isset($_SESSION['user_id'])) { 
    echo json_encode(['error' => 'not_logged']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);

// Cek apakah user adalah developer atau admin
$user_check = $conn->prepare("SELECT level FROM users WHERE id = ?");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$user_data = $user_check->get_result()->fetch_assoc();

// Developer harus level 50+, atau cek role jika ada
// Untuk sekarang, kami check berdasarkan level user
if(!$user_data || $user_data['level'] < 50) {
    echo json_encode(['error' => 'not_authorized']);
    exit;
}

if(!$post_id) {
    echo json_encode(['error' => 'no_post']);
    exit;
}

// Toggle pin status
$check = $conn->prepare("SELECT is_pinned FROM posts WHERE id = ?");
$check->bind_param("i", $post_id);
$check->execute();
$post = $check->get_result()->fetch_assoc();

if(!$post) {
    echo json_encode(['error' => 'post_not_found']);
    exit;
}

$new_status = $post['is_pinned'] ? 0 : 1;
$update = $conn->prepare("UPDATE posts SET is_pinned = ? WHERE id = ?");
$update->bind_param("ii", $new_status, $post_id);

if($update->execute()) {
    echo json_encode(['status' => 'ok', 'is_pinned' => $new_status]);
} else {
    echo json_encode(['error' => 'update_failed']);
}
?>
