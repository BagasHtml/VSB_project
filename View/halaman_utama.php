<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, title, level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(isset($_POST['upload'])) {
    $caption = $_POST['caption'];
    $tags = $_POST['tags'] ?? '';
    $image = $_FILES['image']['name'] ?? '';
    $tmp = $_FILES['image']['tmp_name'] ?? '';
    if($image && move_uploaded_file($tmp, "../uploads/" . $image)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, image, caption, tags) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $image, $caption, $tags);
        $stmt->execute();
    }
}

// Ambil semua post
$posts = $conn->query("
    SELECT posts.*, users.username, users.title, users.level
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Knowledge Battle - Forum Diskusi</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  body { font-family: 'Poppins', sans-serif; overflow: hidden; }
  main { height: 100vh; overflow-y: auto; padding-right: 10px; }
  .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
</style>
</head>
<body class="bg-gray-900 text-white">

<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10">
  <nav class="max-w-7xl mx-auto flex justify-between items-center py-4 px-4 lg:px-8">
    <div class="text-2xl md:text-3xl font-bold"><span class="text-gray-100">Knowledge</span><span class="text-red-600">Battle</span></div>
    <div class="hidden md:flex gap-3 items-center">
      <div class="text-white">Halo! <?= htmlspecialchars($user['username']) ?></div>
      <span class="px-2 py-0.5 bg-purple-600 rounded-full"><?= htmlspecialchars($user['title']) ?></span>
      <span class="px-2 py-0.5 bg-yellow-500 rounded-full">Lvl <?= $user['level'] ?></span>
      <div class="text-white">
        <form action="../service/logout.php">
          <input type="submit" value="logout">
        </form>
      </div>
    </div>
  </nav>
</header>

<main class="pt-28 max-w-4xl mx-auto px-4">

  <!-- Form Upload Thread -->
  <div class="bg-gray-800/30 glass p-6 rounded-2xl mb-8">
    <h2 class="text-xl font-bold mb-4">Buat Thread Baru</h2>
    <form action="" method="post" enctype="multipart/form-data" class="flex flex-col gap-3">
      <textarea name="caption" placeholder="Tulis konten thread..." class="border p-2 rounded bg-gray-900/50" required></textarea>
      <input type="file" name="image" class="border p-2 rounded bg-gray-900/50">
      <input type="text" name="tags" placeholder="#tag1 #tag2" class="border p-2 rounded bg-gray-900/50">
      <button type="submit" name="upload" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-white font-semibold mt-2">Buat Thread</button>
    </form>
  </div>

  <!-- Threads List -->
  <?php while($post = $posts->fetch_assoc()): ?>
    <div class="bg-gray-800/30 glass p-6 rounded-2xl mb-6">
      <div class="flex justify-between items-center mb-3">
        <div>
          <div class="font-bold text-lg"><?=htmlspecialchars($post['username'])?></div>
          <div class="flex items-center gap-2 text-sm text-gray-400">
            <span class="px-2 py-0.5 bg-purple-600 rounded-full"><?=htmlspecialchars($post['title'])?></span>
            <span class="px-2 py-0.5 bg-yellow-500 rounded-full">Lvl <?= $post['level'] ?></span>
          </div>
        </div>
        <div class="text-gray-400 text-sm"><?=date('l, d M Y H:i', strtotime($post['created_at']))?></div>
      </div>

      <?php if($post['image']): ?>
        <img src="../uploads/<?=htmlspecialchars($post['image'])?>" class="w-full rounded-xl mb-4">
      <?php endif; ?>

      <p class="text-gray-200 mb-3"><?=htmlspecialchars($post['caption'])?></p>

      <!-- Tags -->
      <?php
      $tags_string = $post['tags'] ?? '';
      $tags = explode(' ', $tags_string);
      foreach($tags as $tag):
          if(trim($tag) !== ''):
      ?>
        <a href="forum.php?tag=<?=urlencode($tag)?>" class="text-red-500 text-xs bg-red-800/30 px-2 py-0.5 rounded-full mr-1"><?=htmlspecialchars($tag)?></a>
      <?php
          endif;
      endforeach;
      ?>

      <!-- Button untuk komentar -->
      <a href="comments.php?post_id=<?= $post['id'] ?>" class="inline-block mt-3 bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm">Lihat & Balas Komentar</a>
    </div>
  <?php endwhile; ?>

</main>
<footer class="py-12 bg-gray-900 border-t border-white/10 text-center text-gray-400">
  &copy; 2025 Knowledge Battle
</footer>
</body>
</html>
