<?php
// service/controllers/NotificationController.php
require_once __DIR__ . '/../db.php';


class NotificationController {
private $conn;
public function __construct($conn){ $this->conn = $conn; }


public function push($to_user_id, $from_user_id, $type, $post_id = null, $comment_id = null, $message = ''){
$stmt = $this->conn->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, message) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('iisiis', $to_user_id, $from_user_id, $type, $post_id, $comment_id, $message);
$stmt->execute();
}


public function poll($user_id){
$stmt = $this->conn->prepare("SELECT n.*, u.username as from_username FROM notifications n JOIN users u ON n.from_user_id = u.id WHERE n.user_id = ? AND n.is_read = 0 ORDER BY n.created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
return $stmt->get_result();
}
}