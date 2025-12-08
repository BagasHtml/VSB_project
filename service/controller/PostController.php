<?php
require_once __DIR__ . '/../db.php';


class PostController {
private $conn;
public function __construct($conn) { $this->conn = $conn; }


public function createPost($user_id, $caption, $tags, $fileArr) {
$imageName = null;
if($fileArr && !empty($fileArr['name'])){
$ext = pathinfo($fileArr['name'], PATHINFO_EXTENSION);
$imageName = 'post_'.time().rand(10,999).'.'.$ext;
$dest = __DIR__ . '/../../uploads/posts/' . $imageName;
if(!move_uploaded_file($fileArr['tmp_name'], $dest)){
$imageName = null;
}
}


if($imageName){
$stmt = $this->conn->prepare("INSERT INTO posts (user_id, image, caption, tags) VALUES (?, ?, ?, ?)");
$stmt->bind_param('isss', $user_id, $imageName, $caption, $tags);
} else {
$stmt = $this->conn->prepare("INSERT INTO posts (user_id, caption, tags) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $user_id, $caption, $tags);
}
$stmt->execute();
return $this->conn->insert_id;
}


public function fetchPostsWithUser($current_user_id){
$sql = "SELECT posts.*, u.username, u.title, u.level, COALESCE(u.profile_image,u.profile_pic) AS profile_image,
(SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.parent_id IS NULL) AS comment_count,
(SELECT COUNT(*) FROM post_likes WHERE post_likes.post_id = posts.id) AS like_count,
(SELECT COUNT(*) FROM post_likes WHERE post_likes.post_id = posts.id AND post_likes.user_id = ?) AS user_liked
FROM posts JOIN users u ON posts.user_id = u.id
ORDER BY posts.created_at DESC";
$stmt = $this->conn->prepare($sql);
$stmt->bind_param('i', $current_user_id);
$stmt->execute();
return $stmt->get_result();
}
}