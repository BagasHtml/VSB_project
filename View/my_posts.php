<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT username, profile_pic, title, level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle post deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $post_id = $_GET['delete'];
    
    // Verify ownership
    $check = $conn->prepare("SELECT id, image FROM posts WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $post_id, $user_id);
    $check->execute();
    $post = $check->get_result()->fetch_assoc();
    
    if($post) {
        // Delete image if exists
        if($post['image'] && file_exists("../uploads/" . $post['image'])) {
            unlink("../uploads/" . $post['image']);
        }
        
        // Delete post (cascade will handle comments, likes, notifications)
        $delete = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $delete->bind_param("i", $post_id);
        $delete->execute();
        
        header("Location: my_posts.php?success=deleted");
        exit;
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT p.*, 
    (SELECT COUNT(*) FROM comments WHERE post_id=p.id) AS comment_count,
    (SELECT COUNT(*) FROM post_likes WHERE post_id=p.id) AS like_count
    FROM posts p 
    WHERE p.user_id = ?";

$params = [$user_id];
$types = "i";

if($search) {
    $query .= " AND (p.caption LIKE ? OR p.tags LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$posts = $stmt->get_result();

$total_posts = $posts->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Saya - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  body{font-family:'Poppins',sans-serif;}
  .glass{background:rgba(255,255,255,0.05);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1);}
  .gradient-text{background:linear-gradient(to right,#ef4444,#dc2626);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
  .modal {
    display: none;
    position: fixed;
    z-index: 100;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
  }
  .modal.active {
    display: flex;
    justify-content: center;
    align-items: center;
  }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen">

<!-- HEADER -->
<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10">
  <nav class="max-w-7xl mx-auto flex justify-between items-center py-3 px-3 lg:px-8">
    <a href="settings.php" class="flex items-center gap-2 glass px-4 py-2 rounded-full hover:bg-red-600/20 transition">
      <i class="bi bi-arrow-left"></i> <span>Kembali</span>
    </a>
    <div class="text-xl md:text-3xl font-bold"><span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span></div>
    <div class="w-32"></div>
  </nav>
</header>

<!-- CONTENT -->
<main class="pt-28 pb-12 max-w-6xl mx-auto px-4">

  <!-- SUCCESS MESSAGE -->
  <?php if(isset($_GET['success'])): ?>
  <div class="glass p-4 rounded-xl mb-6 border border-green-500/50 bg-green-500/10">
    <div class="flex items-center gap-3">
      <i class="bi bi-check-circle text-2xl text-green-400"></i>
      <p class="text-green-400">Post berhasil dihapus!</p>
    </div>
  </div>
  <?php endif; ?>

  <!-- HEADER SECTION -->
  <div class="glass p-6 md:p-8 rounded-3xl mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <?php if(!empty($user['profile_pic'])): ?>
          <img src="../uploads/profile/<?= htmlspecialchars($user['profile_pic']) ?>" 
               class="w-16 h-16 rounded-full object-cover border-2 border-red-500">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-2xl font-bold">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div>
          <h1 class="text-2xl md:text-3xl font-bold">Post Saya</h1>
          <p class="text-gray-400">Kelola semua postingan Anda</p>
        </div>
      </div>
      <div class="glass px-6 py-3 rounded-xl text-center">
        <div class="text-3xl font-bold gradient-text"><?= $total_posts ?></div>
        <div class="text-sm text-gray-400">Total Posts</div>
      </div>
    </div>
  </div>

  <!-- SEARCH & FILTER -->
  <div class="glass p-4 md:p-6 rounded-2xl mb-6">
    <form method="get" class="flex flex-col md:flex-row gap-3">
      <div class="flex-1 relative">
        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
          placeholder="Cari postingan..." 
          class="w-full glass p-3 pl-12 rounded-xl focus:outline-none focus:border-red-500 border border-white/10 transition">
      </div>
      <button type="submit" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 px-6 py-3 rounded-xl font-semibold transition">
        <i class="bi bi-search mr-2"></i>Cari
      </button>
      <?php if($search): ?>
        <a href="my_posts.php" class="glass hover:bg-white/10 px-6 py-3 rounded-xl font-semibold transition text-center">
          <i class="bi bi-x-lg mr-2"></i>Reset
        </a>
      <?php endif; ?>
    </form>
  </div>

  <!-- POSTS LIST -->
  <?php if($total_posts > 0): ?>
    <div class="space-y-4 md:space-y-6">
      <?php while($post = $posts->fetch_assoc()): ?>
        <div class="glass p-4 md:p-6 rounded-2xl hover:border-red-500/30 transition">
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
                <i class="bi bi-calendar"></i>
                <span><?= date('d M Y, H:i', strtotime($post['created_at'])) ?></span>
                <?php if($post['is_pinned']): ?>
                  <span class="px-2 py-1 bg-yellow-500/20 rounded-full text-yellow-400 text-xs flex items-center gap-1">
                    <i class="bi bi-pin-angle-fill"></i>Pinned
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php if($post['image']): ?>
            <img src="../uploads/<?= htmlspecialchars($post['image']) ?>" 
              class="w-full max-h-96 object-cover rounded-xl mb-4">
          <?php endif; ?>

          <p class="text-gray-200 mb-4 line-clamp-3"><?= nl2br(htmlspecialchars($post['caption'])) ?></p>

          <?php if($post['tags']): ?>
            <div class="flex flex-wrap gap-2 mb-4">
              <?php
              $tags = array_filter(explode(' ', $post['tags']));
              foreach($tags as $tag):
              ?>
                <span class="text-xs bg-red-900/30 text-red-400 px-3 py-1 rounded-full"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Stats & Actions -->
          <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-white/10">
            <div class="flex items-center gap-4 text-sm text-gray-400">
              <div class="flex items-center gap-2">
                <i class="bi bi-chat-dots"></i>
                <span><?= $post['comment_count'] ?> Comments</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="bi bi-heart"></i>
                <span><?= $post['like_count'] ?> Likes</span>
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <a href="comments.php?post_id=<?= $post['id'] ?>" 
                class="glass hover:bg-blue-600/20 px-4 py-2 rounded-xl transition text-sm flex items-center gap-2">
                <i class="bi bi-eye"></i>
                <span class="hidden md:inline">Lihat</span>
              </a>
              <a href="halaman_utama.php#post-<?= $post['id'] ?>" 
                class="glass hover:bg-purple-600/20 px-4 py-2 rounded-xl transition text-sm flex items-center gap-2">
                <i class="bi bi-pencil"></i>
                <span class="hidden md:inline">Edit</span>
              </a>
              <button onclick="confirmDelete(<?= $post['id'] ?>)" 
                class="glass hover:bg-red-600/50 px-4 py-2 rounded-xl transition text-sm flex items-center gap-2 text-red-400">
                <i class="bi bi-trash"></i>
                <span class="hidden md:inline">Hapus</span>
              </button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="glass p-12 rounded-3xl text-center">
      <i class="bi bi-inbox text-6xl text-gray-600 mb-4"></i>
      <h3 class="text-xl font-bold mb-2">Belum Ada Postingan</h3>
      <p class="text-gray-400 mb-6">
        <?= $search ? 'Tidak ada postingan yang cocok dengan pencarian Anda' : 'Mulai berbagi pemikiran Anda dengan komunitas!' ?>
      </p>
      <a href="halaman_utama.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 px-6 py-3 rounded-xl font-semibold text-white transition">
        <i class="bi bi-plus-lg"></i> Buat Postingan Baru   
      </a>
    </div>
    <?php endif; ?>
</main>
<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteModal" class="modal">
    <div class="bg-gray-800 rounded-2xl p-6 max-w-md w-full text-center">
        <h2 class="text-2xl font-bold mb-4">Konfirmasi Hapus Postingan</h2>
        <p class="text-gray-400 mb-6">Apakah Anda yakin ingin menghapus postingan ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="flex justify-center gap-4">
        <button onclick="closeModal()" class="glass px-6 py-2 rounded-xl hover:bg-white/10 transition font-semibold">Batal</button>
        <a id="confirmDeleteBtn" href="#" class="bg-red-600
    px-6 py-2 rounded-xl hover:bg-red-700 transition font-semibold text-white">Hapus</a>
        </div>
    </div>
</div>
<script>
  function confirmDelete(postId) {
    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.href = `my_posts.php?delete=${postId}`;
    modal.classList.add('active');
  }

  function closeModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('active');
  }
</script>
</body>
</html>