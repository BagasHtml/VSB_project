<?php
session_start();
include __DIR__ . '/../service/db.php';
header('Content-Type: application/json');


if(!isset($_SESSION['user_id'])){ echo json_encode(['error'=>'not_logged']); exit; }
$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);


if(!$post_id){ echo json_encode(['error'=>'no_post']); exit; }


$check = $conn->prepare("SELECT id FROM post_likes WHERE post_id=? AND user_id=?");
$check->bind_param('ii',$post_id,$user_id);
$check->execute();
$res = $check->get_result();


if($res->num_rows>0){
$del = $conn->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?");
$del->bind_param('ii',$post_id,$user_id);
$del->execute();
$action = 'unliked';
} else {
$ins = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?,?)");
$ins->bind_param('ii',$post_id,$user_id);
$ins->execute();
$action = 'liked';


// push notification to post owner
$post_owner = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
$post_owner->bind_param('i',$post_id);
$post_owner->execute();
$owner = $post_owner->get_result()->fetch_assoc();
if($owner && $owner['user_id'] != $user_id){
$msg = 'Someone liked your post';
$notif = $conn->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, message) VALUES (?, ?, 'like', ?, ?)");
$notif->bind_param('iiis', $owner['user_id'], $user_id, $post_id, $msg);
$notif->execute();
}
}


// return new counts
$cnt = $conn->query("SELECT COUNT(*) as c FROM post_likes WHERE post_id = $post_id")->fetch_assoc()['c'];
echo json_encode(['status'=>'ok','action'=>$action,'count'=>$cnt]);