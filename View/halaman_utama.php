<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if(isset($_POST['upload'])) {
    $caption = $_POST['caption'];
    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $path = "../uploads/" . $image;
    if(move_uploaded_file($tmp, $path)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, image, caption) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $image, $caption);
        $stmt->execute();
    }
}

$posts = $conn->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="bg-red p-4 rounded-lg shadow mb-6">
    <form action="../service/logout.php">
        <input type="submit" class="bg-gray-800 text-white px-4 rounded" value="logout">
    </form>
</div>

<div class="max-w-3xl mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Halo, <?=htmlspecialchars($user['username'])?></h1>

    <!-- Form Upload Post -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form action="" method="post" enctype="multipart/form-data" class="flex flex-col gap-4">
            <input type="file" name="image" required class="border p-2 rounded">
            <textarea name="caption" placeholder="Tulis caption..." class="border p-2 rounded"></textarea>
            <button type="submit" name="upload" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Upload</button>
        </form>
    </div>

    <!-- List Post -->
    <?php while($post = $posts->fetch_assoc()): ?>
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="font-bold"><?=htmlspecialchars($post['username'])?></div>
                <div class="text-gray-500 text-sm"><?=date('d M Y H:i', strtotime($post['created_at']))?></div>
            </div>
            <img src="../uploads/<?=htmlspecialchars($post['image'])?>" class="w-full rounded mb-2">
            <p class="mb-2"><?=htmlspecialchars($post['caption'])?></p>

            <!-- Form Komentar -->
            <form action="../service/add_comment.php" method="post" class="flex gap-2">
                <input type="hidden" name="post_id" value="<?=$post['id']?>">
                <input type="text" name="comment" placeholder="Tulis komentar..." class="border p-2 rounded flex-1" required>
                <button type="submit" class="bg-gray-800 text-white px-4 rounded">Kirim</button>
            </form>

            <!-- Komentar -->
            <div class="mt-4 space-y-2">
                <?php
                $comments = $conn->query("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id=".$post['id']." ORDER BY created_at ASC");
                while($c = $comments->fetch_assoc()):
                ?>
                    <div class="text-sm"><span class="font-bold"><?=htmlspecialchars($c['username'])?>:</span> <?=htmlspecialchars($c['comment'])?></div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
