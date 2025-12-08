<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT username, email, title, level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get statistics
$post_count = $conn->query("SELECT COUNT(*) as count FROM posts WHERE user_id = $user_id")->fetch_assoc()['count'];
$comment_count = $conn->query("SELECT COUNT(*) as count FROM comments WHERE user_id = $user_id")->fetch_assoc()['count'];
$likes_received = $conn->query("SELECT COUNT(*) as count FROM post_likes pl JOIN posts p ON pl.post_id = p.id WHERE p.user_id = $user_id")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  body { font-family: 'Poppins', sans-serif; }
  .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
  .gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen">

<!-- Header -->
<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10">
  <nav class="max-w-7xl mx-auto flex justify-between items-center py-3 px-3 lg:px-8">
    <a href="halaman_utama.php" class="flex items-center gap-2 glass px-4 py-2 rounded-full hover:bg-red-600/20 transition">
      <i class="bi bi-arrow-left"></i>
      <span>Back to Forum</span>
    </a>
    <div class="text-xl md:text-3xl font-bold">
      <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
    </div>
    <div class="w-32"></div>
  </nav>
</header>

<main class="pt-28 pb-12 max-w-4xl mx-auto px-4">
  
  <!-- Profile Card -->
  <div class="glass p-8 rounded-3xl mb-8">
    <div class="flex items-center gap-6 mb-6">
      <div class="w-24 h-24 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-4xl font-bold">
        <?= strtoupper(substr($user['username'], 0, 1)) ?>
      </div>
      <div>
        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($user['username']) ?></h1>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 bg-purple-600/30 rounded-full text-sm"><?= htmlspecialchars($user['title']) ?></span>
          <span class="px-3 py-1 bg-yellow-500/30 rounded-full text-sm">Level <?= $user['level'] ?></span>
        </div>
        <p class="text-gray-400 mt-2"><?= htmlspecialchars($user['email']) ?></p>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4">
      <div class="glass p-4 rounded-xl text-center">
        <div class="text-2xl font-bold gradient-text"><?= $post_count ?></div>
        <div class="text-sm text-gray-400">Posts</div>
      </div>
      <div class="glass p-4 rounded-xl text-center">
        <div class="text-2xl font-bold text-blue-400"><?= $comment_count ?></div>
        <div class="text-sm text-gray-400">Comments</div>
      </div>
      <div class="glass p-4 rounded-xl text-center">
        <div class="text-2xl font-bold text-red-400"><?= $likes_received ?></div>
        <div class="text-sm text-gray-400">Likes Received</div>
      </div>
    </div>
  </div>

  <!-- Account Settings -->
  <div class="glass p-8 rounded-3xl mb-8">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i class="bi bi-gear text-red-500"></i>
      Pengaturan Akun
    </h2>

    <div class="space-y-4">
      <a href="edit_profile.php" class="flex items-center justify-between p-4 glass rounded-xl hover:bg-white/10 transition">
        <div class="flex items-center gap-3">
          <i class="bi bi-person-circle text-2xl text-blue-400"></i>
          <div>
            <div class="font-semibold">Edit Profil</div>
            <div class="text-sm text-gray-400">Ubah username, title, dan bio</div>
          </div>
        </div>
        <i class="bi bi-chevron-right text-gray-400"></i>
      </a>

      <a href="change_password.php" class="flex items-center justify-between p-4 glass rounded-xl hover:bg-white/10 transition">
        <div class="flex items-center gap-3">
          <i class="bi bi-key text-2xl text-green-400"></i>
          <div>
            <div class="font-semibold">Ganti Password</div>
            <div class="text-sm text-gray-400">Update password akun Anda</div>
          </div>
        </div>
        <i class="bi bi-chevron-right text-gray-400"></i>
      </a>

      <a href="my_posts.php" class="flex items-center justify-between p-4 glass rounded-xl hover:bg-white/10 transition">
        <div class="flex items-center gap-3">
          <i class="bi bi-file-earmark-text text-2xl text-purple-400"></i>
          <div>
            <div class="font-semibold">Post Saya</div>
            <div class="text-sm text-gray-400">Lihat dan kelola semua post Anda</div>
          </div>
        </div>
        <i class="bi bi-chevron-right text-gray-400"></i>
      </a>
    </div>
  </div>

  <!-- Danger Zone -->
  <div class="glass p-8 rounded-3xl border-red-500/30">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2 text-red-400">
      <i class="bi bi-exclamation-triangle"></i>
      Danger Zone
    </h2>

    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
      <h3 class="font-bold mb-2">Hapus Akun Permanen</h3>
      <p class="text-sm text-gray-400 mb-4">
        Menghapus akun akan menghapus semua data Anda secara permanen. Tindakan ini tidak dapat dibatalkan.
      </p>
      <a href="delete_account.php" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 px-6 py-3 rounded-xl font-semibold transition">
        <i class="bi bi-trash"></i>
        Hapus Akun
      </a>
    </div>
  </div>

</main>

</body>
</html>