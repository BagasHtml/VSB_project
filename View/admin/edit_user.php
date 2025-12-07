<?php
session_start();
include '../../service/db.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(isset($_POST['update'])) {
    $title = $_POST['title'];
    $level = $_POST['level'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET title=?, level=?, role=? WHERE id=?");
    $stmt->bind_param("ssis", $title, $level, $role, $id);
    $stmt->execute();

    header("Location: admin_panel.php");
    exit();
}
?>
<form method="post">
    <label>Title: <input type="text" name="title" value="<?= htmlspecialchars($user['title']) ?>"></label><br>
    <label>Level: <input type="number" name="level" value="<?= $user['level'] ?>"></label><br>
    <label>Role:
        <select name="role">
            <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
            <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        </select>
    </label><br>
    <button type="submit" name="update">Update</button>
</form>
