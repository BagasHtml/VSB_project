<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT posts.*, users.username, users.title, users.level
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(isset($_POST['comment'])) {
    $comment = $_POST['comment'];
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $comment);
    $stmt->execute();
    header("Location: comments.php?post_id=".$post_id);
    exit;
}

$comments = $conn->query("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id=".$post_id." ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Komentar Thread</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-6">

<a href="halaman_utama.php" class="text-red-500 mb-4 inline-block">&larr; Kembali ke Halaman Utama</a>

<div class="bg-gray-800/30 glass p-6 rounded-2xl mb-6">
  <div class="flex justify-between items-center mb-3">
    <div>
      <div class="font-bold text-lg"><?= htmlspecialchars($post['username']) ?></div>
      <div class="flex items-center gap-2 text-sm text-gray-400">
        <span class="px-2 py-0.5 bg-purple-600 rounded-full"><?= htmlspecialchars($post['title']) ?></span>
        <span class="px-2 py-0.5 bg-yellow-500 rounded-full">Lvl <?= $post['level'] ?></span>
      </div>
    </div>
    <div class="text-gray-400 text-sm"><?= date('l, d M Y H:i', strtotime($post['created_at'])) ?></div>
  </div>
  <p class="text-gray-200 mb-3"><?= htmlspecialchars($post['caption']) ?></p>

  <?php if($post['image']): ?>
    <img src="../uploads/<?= htmlspecialchars($post['image']) ?>" class="w-full rounded-xl mb-4">
  <?php endif; ?>
</div>

<!-- Form komentar -->
<form method="post" class="flex flex-col gap-3 mb-6">
  <textarea name="comment" placeholder="Tulis komentar..." class="border p-2 rounded bg-gray-900/50" required></textarea>
  <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-white font-semibold">Kirim Komentar</button>
</form>

<!-- Daftar komentar -->
<?php while($c = $comments->fetch_assoc()): ?>
  <div class="bg-gray-800/30 glass p-4 rounded-2xl mb-3">
    <span class="font-bold"><?= htmlspecialchars($c['username']) ?>:</span>
    <?= htmlspecialchars($c['comment']) ?>
    <div class="text-gray-400 text-xs"><?= date('H:i, d M Y', strtotime($c['created_at'])) ?></div>
  </div>
<?php endwhile; ?>

</body>
</html>
