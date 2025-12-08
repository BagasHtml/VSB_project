<?php
session_start();
include __DIR__ . '/../service/db.php';
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
if($q===''){ echo json_encode([]); exit; }
$stmt = $conn->prepare("SELECT id, username FROM users WHERE username LIKE ? LIMIT 10");
$like = $q.'%';
$stmt->bind_param('s',$like);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
?>