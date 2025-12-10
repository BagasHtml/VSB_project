<?php
// service/check_username.php
header('Content-Type: application/json');
include 'db.php';

if (!isset($_GET['username'])) {
    echo json_encode(['available' => false, 'message' => 'Username required']);
    exit;
}

$username = trim($_GET['username']);

// Validate format
if (strlen($username) < 3 || strlen($username) > 20) {
    echo json_encode(['available' => false, 'message' => 'Username harus 3-20 karakter']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    echo json_encode(['available' => false, 'message' => 'Format username tidak valid']);
    exit;
}

// Check database
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['available' => false, 'message' => 'Username sudah digunakan']);
} else {
    echo json_encode(['available' => true, 'message' => 'Username tersedia']);
}

$stmt->close();
$conn->close();
?>