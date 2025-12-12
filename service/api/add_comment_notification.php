<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? 0;
    $comment_id = $_POST['comment_id'] ?? 0;
    $comment_text = $_POST['comment_text'] ?? '';
    
    // Get post owner
    $postStmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $postStmt->bind_param("i", $post_id);
    $postStmt->execute();
    $post = $postStmt->get_result()->fetch_assoc();
    
    if (!$post) {
        http_response_code(404);
        die(json_encode(['status' => 'error', 'message' => 'Post not found']));
    }
    
    $post_owner = $post['user_id'];
    
    // Get commenter info
    $commenterStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $commenterStmt->bind_param("i", $user_id);
    $commenterStmt->execute();
    $commenter = $commenterStmt->get_result()->fetch_assoc();
    
    // Notify post owner about comment (if not the commenter)
    if ($post_owner != $user_id) {
        $message = ($commenter['username'] ?? 'Someone') . " commented on your post";
        
        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, message, created_at)
            VALUES (?, ?, 'comment', ?, ?, ?, NOW())
        ");
        $notifStmt->bind_param("iiis", $post_owner, $user_id, $post_id, $comment_id, $message);
        $notifStmt->execute();
    }
    
    // Handle mentions in comment
    preg_match_all('/@(\w+)/', $comment_text, $mentions);
    if (!empty($mentions[1])) {
        foreach (array_unique($mentions[1]) as $mentioned) {
            $userStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $userStmt->bind_param("s", $mentioned);
            $userStmt->execute();
            $mentionedUser = $userStmt->get_result()->fetch_assoc();
            
            if ($mentionedUser && $mentionedUser['id'] != $user_id) {
                $message = ($commenter['username'] ?? 'Someone') . " mentioned you in a comment";
                
                // Check if notification already exists
                $checkStmt = $conn->prepare("
                    SELECT id FROM notifications 
                    WHERE user_id = ? AND from_user_id = ? AND type = 'mention' AND comment_id = ?
                ");
                $checkStmt->bind_param("iii", $mentionedUser['id'], $user_id, $comment_id);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows == 0) {
                    $mentionStmt = $conn->prepare("
                        INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, message, created_at)
                        VALUES (?, ?, 'mention', ?, ?, ?, NOW())
                    ");
                    $mentionStmt->bind_param("iiis", $mentionedUser['id'], $user_id, $post_id, $comment_id, $message);
                    $mentionStmt->execute();
                }
            }
        }
    }
    
    echo json_encode(['status' => 'ok', 'message' => 'Notifications created']);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>