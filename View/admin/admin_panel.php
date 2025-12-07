<?php
session_start();
include '../../service/db.php';

if(
    !isset($_SESSION['admin_id']) || 
    !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')
) {
    echo "Access denied";
    exit();
}

$users = $conn->query("SELECT id, username, email, role, title, level FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../../Design/Css/admin.css">
</head>
<body>
    <h1>Admin Panel</h1>
    <a href="logout.php">Logout</a>

    <h2>User List</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Title</th>
            <th>Level</th>
            <th>Aksi</th>
        </tr>
        <?php while($user = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['role'] ?></td>
            <td><?= htmlspecialchars($user['title']) ?></td>
            <td><?= $user['level'] ?></td>
            <td>
                <a href="edit_user.php?id=<?= $user['id'] ?>">Edit</a> |
                <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Yakin?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
