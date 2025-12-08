<?php
// service/controllers/UserController.php
require_once __DIR__ . '/../db.php';


class UserController {
private $conn;
public function __construct($conn){ $this->conn = $conn; }


public function searchUsers($q, $limit = 10){
$like = $q . '%';
$stmt = $this->conn->prepare("SELECT id, username FROM users WHERE username LIKE ? LIMIT ?");
$stmt->bind_param('si', $like, $limit);
$stmt->execute();
return $stmt->get_result();
}
}