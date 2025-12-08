<?php
session_start();
include '../../service/db.php';

// Check authorization
if(
    !isset($_SESSION['admin_id']) || 
    !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')
) {
    header("Location: admin_login.php");
    exit();
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$admin_stmt = $conn->prepare("SELECT username, role, email FROM users WHERE id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin = $admin_stmt->get_result()->fetch_assoc();

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_posts = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
$total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
$pinned_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE is_pinned = 1")->fetch_assoc()['count'];

// Get users list
$users = $conn->query("SELECT id, username, email, role, title, level FROM users ORDER BY id DESC");

// Get posts list
$posts = $conn->query("SELECT p.id, p.caption, p.is_pinned, u.username, (SELECT COUNT(*) FROM comments WHERE post_id=p.id) as comment_count, (SELECT COUNT(*) FROM post_likes WHERE post_id=p.id) as like_count, p.created_at FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  
  * { box-sizing: border-box; }
  
  body { 
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
  }
  
  .glass { 
    background: rgba(255,255,255,0.05); 
    backdrop-filter: blur(10px); 
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
  }
  
  .gradient-text { 
    background: linear-gradient(to right, #ef4444, #dc2626); 
    -webkit-background-clip: text; 
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .sidebar { 
    width: 260px;
    position: relative;
    z-index: 10;
    transition: all 0.3s ease;
  }
  
  .main-content { 
    flex: 1;
    overflow-y: auto;
  }
  
  .stat-card { 
    transition: all 0.3s ease;
    transform: translateZ(0);
  }
  
  .stat-card:hover { 
    transform: translateY(-5px);
  }
  
  .nav-item { 
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
  }
  
  .nav-item.active { 
    background: rgba(239, 68, 68, 0.2); 
    border-left: 3px solid #ef4444;
  }
  
  .nav-item:hover { 
    background: rgba(255,255,255,0.05);
  }
  
  .tab-content { 
    animation: fadeIn 0.3s ease;
  }
  
  .tab-content.hidden { 
    display: none !important;
  }
  
  @keyframes fadeIn {
    from { 
      opacity: 0; 
      transform: translateY(5px);
    }
    to { 
      opacity: 1; 
      transform: translateY(0);
    }
  }
  
  table tbody tr { 
    transition: all 0.2s ease;
  }
  
  table tbody tr:hover { 
    background: rgba(255,255,255,0.1);
  }
  
  /* Mobile Layout */
  @media (max-width: 768px) {
    .flex {
      flex-direction: column;
    }
    
    .sidebar {
      width: 100%;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: auto;
      border-right: none;
      border-top: 1px solid rgba(255,255,255,0.1);
      max-height: 60px;
      overflow-x: auto;
      flex-direction: row;
      padding: 0 !important;
    }
    
    .sidebar > div:first-child {
      display: none;
    }
    
    nav.flex-1 {
      flex: 1 !important;
      padding: 5px 10px !important;
      flex-direction: row !important;
      gap: 0 !important;
      space-y: 0 !important;
    }
    
    nav.flex-1 button {
      flex: 1;
      padding: 8px 12px !important;
      font-size: 12px !important;
      gap: 0 !important;
    }
    
    nav.flex-1 i {
      font-size: 16px !important;
      margin: 0 !important;
    }
    
    nav.flex-1 span {
      display: none;
    }
    
    .sidebar > div:last-child {
      display: none;
    }
    
    .main-content {
      padding-bottom: 80px;
      margin-bottom: 20px;
    }
    
    .stat-card {
      padding: 15px 12px !important;
    }
    
    .stat-card h3 {
      font-size: 24px !important;
      margin: 5px 0 !important;
    }
    
    .stat-card i {
      font-size: 32px !important;
    }
    
    .input-box {
      margin: 15px 0 !important;
    }
    
    .form-box, .glass {
      padding: 15px !important;
      border-radius: 12px;
    }
    
    table {
      font-size: 12px !important;
    }
    
    table th, table td {
      padding: 8px 4px !important;
    }
    
    .overflow-x-auto {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    
    h2 {
      font-size: 18px !important;
    }
  }
  
  /* Tablet Layout */
  @media (min-width: 769px) and (max-width: 1024px) {
    .sidebar {
      width: 200px;
    }
    
    .stat-card {
      padding: 15px !important;
    }
    
    h2 {
      font-size: 20px !important;
    }
    
    table {
      font-size: 13px;
    }
    
    table th, table td {
      padding: 10px 6px;
    }
  }
  
  /* Large screens */
  @media (min-width: 1025px) {
    .sidebar {
      width: 260px;
    }
    
    .main-content {
      padding: 2rem;
    }
  }
  
  /* Extra small devices */
  @media (max-width: 480px) {
    .grid {
      grid-template-columns: 1fr !important;
    }
    
    .wrapper {
      width: 100%;
    }
    
    #search-user, #search-post {
      width: 100% !important;
      max-width: 100%;
    }
    
    button.btn {
      padding: 10px 8px !important;
      font-size: 13px !important;
    }
    
    .form-group {
      grid-template-columns: 1fr !important;
    }
  }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">

<div class="flex h-screen">
  <!-- Sidebar -->
  <div class="sidebar glass border-r border-white/10 flex flex-col">
    <!-- Logo -->
    <div class="p-6 border-b border-white/10">
      <div class="text-xl font-bold">
        <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
      </div>
      <p class="text-xs text-gray-500 mt-1">Admin Panel</p>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
      <button onclick="switchTab('dashboard')" class="tab-btn active nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm" data-tab="dashboard">
        <i class="bi bi-speedometer2 text-lg"></i>
        <span>Dashboard</span>
      </button>
      <button onclick="switchTab('users')" class="tab-btn nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm" data-tab="users">
        <i class="bi bi-people text-lg"></i>
        <span>Kelola User</span>
      </button>
      <button onclick="switchTab('posts')" class="tab-btn nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm" data-tab="posts">
        <i class="bi bi-file-text text-lg"></i>
        <span>Kelola Post</span>
      </button>
    </nav>

    <!-- Admin Info -->
    <div class="p-4 border-t border-white/10 space-y-3">
      <div class="glass p-3 rounded-lg text-xs">
        <p class="text-gray-400">Admin</p>
        <p class="font-semibold text-sm"><?= htmlspecialchars($admin['username']) ?></p>
        <p class="text-gray-500 text-xs mt-1"><?= ucfirst($admin['role']) ?></p>
      </div>
      <a href="logout.php" class="glass hover:bg-red-600/20 w-full text-center py-2 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-2">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content overflow-y-auto">
    <!-- Header -->
    <div class="glass border-b border-white/10 p-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">Dashboard</h1>
          <p class="text-gray-400 text-sm mt-1">Selamat datang kembali, <?= htmlspecialchars($admin['username']) ?>! 👋</p>
        </div>
        <div class="text-right text-sm text-gray-400">
          <p id="current-date"></p>
          <p id="current-time"></p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-8">
      <!-- Dashboard Tab -->
      <div id="tab-dashboard" class="tab-content">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <!-- Total Users -->
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm">Total User</p>
                <h3 class="text-3xl font-bold mt-2"><?= $total_users ?></h3>
              </div>
              <i class="bi bi-people text-4xl text-blue-500 opacity-20"></i>
            </div>
          </div>

          <!-- Total Posts -->
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm">Total Post</p>
                <h3 class="text-3xl font-bold mt-2"><?= $total_posts ?></h3>
              </div>
              <i class="bi bi-file-text text-4xl text-green-500 opacity-20"></i>
            </div>
          </div>

          <!-- Total Comments -->
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm">Total Komentar</p>
                <h3 class="text-3xl font-bold mt-2"><?= $total_comments ?></h3>
              </div>
              <i class="bi bi-chat-dots text-4xl text-purple-500 opacity-20"></i>
            </div>
          </div>

          <!-- Pinned Posts -->
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm">Post Ter-pin</p>
                <h3 class="text-3xl font-bold mt-2"><?= $pinned_posts ?></h3>
              </div>
              <i class="bi bi-pin-fill text-4xl text-yellow-500 opacity-20"></i>
            </div>
          </div>
        </div>

        <!-- Welcome Message -->
        <div class="glass p-6 rounded-2xl text-center">
          <h2 class="text-2xl font-bold mb-2">Selamat Datang di Admin Panel 👋</h2>
          <p class="text-gray-400">Gunakan navigasi di sebelah kiri untuk mengelola user dan post forum Knowledge Battle Anda.</p>
        </div>
      </div>

      <!-- User Management Section -->
      <div id="tab-users" class="tab-content hidden glass p-6 rounded-2xl">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Kelola User</h2>
          <input type="text" id="search-user" placeholder="Cari user..." 
            class="glass px-4 py-2 rounded-lg text-sm border border-white/10 focus:border-red-500 outline-none transition w-64">
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-white/10">
                <th class="text-left py-3 px-4 text-gray-400">ID</th>
                <th class="text-left py-3 px-4 text-gray-400">Username</th>
                <th class="text-left py-3 px-4 text-gray-400">Email</th>
                <th class="text-left py-3 px-4 text-gray-400">Role</th>
                <th class="text-left py-3 px-4 text-gray-400">Title</th>
                <th class="text-left py-3 px-4 text-gray-400">Level</th>
                <th class="text-left py-3 px-4 text-gray-400">Aksi</th>
              </tr>
            </thead>
            <tbody id="user-table-body">
              <?php while($user = $users->fetch_assoc()): ?>
              <tr class="border-b border-white/10 hover:bg-white/5 transition">
                <td class="py-3 px-4"><?= $user['id'] ?></td>
                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($user['username']) ?></td>
                <td class="py-3 px-4 text-gray-400 text-xs"><?= htmlspecialchars($user['email']) ?></td>
                <td class="py-3 px-4">
                  <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-red-900/30 text-red-300' : ($user['role'] === 'developer' ? 'bg-blue-900/30 text-blue-300' : 'bg-gray-700/30 text-gray-300') ?>">
                    <?= ucfirst($user['role']) ?>
                  </span>
                </td>
                <td class="py-3 px-4"><?= htmlspecialchars($user['title']) ?></td>
                <td class="py-3 px-4 font-semibold"><?= $user['level'] ?></td>
                <td class="py-3 px-4">
                  <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-400 hover:text-blue-300 text-xs font-semibold mr-3">
                    <i class="bi bi-pencil-square"></i> Edit
                  </a>
                  <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="text-red-400 hover:text-red-300 text-xs font-semibold">
                    <i class="bi bi-trash"></i> Hapus
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Posts Management Section -->
      <div id="tab-posts" class="tab-content hidden glass p-6 rounded-2xl">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Kelola Post</h2>
          <input type="text" id="search-post" placeholder="Cari post..." 
            class="glass px-4 py-2 rounded-lg text-sm border border-white/10 focus:border-green-500 outline-none transition w-64">
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-white/10">
                <th class="text-left py-3 px-4 text-gray-400">ID</th>
                <th class="text-left py-3 px-4 text-gray-400">Caption</th>
                <th class="text-left py-3 px-4 text-gray-400">Author</th>
                <th class="text-left py-3 px-4 text-gray-400">Komentar</th>
                <th class="text-left py-3 px-4 text-gray-400">Like</th>
                <th class="text-left py-3 px-4 text-gray-400">Status</th>
                <th class="text-left py-3 px-4 text-gray-400">Aksi</th>
              </tr>
            </thead>
            <tbody id="post-table-body">
              <?php while($post = $posts->fetch_assoc()): ?>
              <tr class="border-b border-white/10 hover:bg-white/5 transition">
                <td class="py-3 px-4"><?= $post['id'] ?></td>
                <td class="py-3 px-4 font-medium max-w-xs truncate"><?= htmlspecialchars(substr($post['caption'], 0, 50)) ?>...</td>
                <td class="py-3 px-4"><?= htmlspecialchars($post['username']) ?></td>
                <td class="py-3 px-4 text-center"><?= $post['comment_count'] ?></td>
                <td class="py-3 px-4 text-center"><?= $post['like_count'] ?></td>
                <td class="py-3 px-4">
                  <?php if($post['is_pinned']): ?>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-900/30 text-yellow-300">
                      <i class="bi bi-pin-fill"></i> Pinned
                    </span>
                  <?php else: ?>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-700/30 text-gray-300">Normal</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4">
                  <button onclick="togglePin(<?= $post['id'] ?>, this)" class="text-yellow-400 hover:text-yellow-300 text-xs font-semibold mr-3" title="Toggle Pin">
                    <i class="bi bi-pin"></i>
                  </button>
                  <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Yakin ingin menghapus post ini?')" class="text-red-400 hover:text-red-300 text-xs font-semibold">
                    <i class="bi bi-trash"></i> Hapus
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Update current date and time
function updateDateTime() {
  const now = new Date();
  const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  
  document.getElementById('current-date').textContent = dateStr;
  document.getElementById('current-time').textContent = timeStr;
}

updateDateTime();
setInterval(updateDateTime, 1000);

// Tab switching function
function switchTab(tabName) {
  // Hide all tabs
  document.querySelectorAll('.tab-content').forEach(content => {
    content.classList.add('hidden');
  });
  
  // Show selected tab
  const selectedTab = document.getElementById(`tab-${tabName}`);
  if (selectedTab) {
    selectedTab.classList.remove('hidden');
  }
  
  // Update button styles
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  
  const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
  if (activeBtn) {
    activeBtn.classList.add('active');
  }
}

// Search functionality for users
document.getElementById('search-user').addEventListener('keyup', function(e) {
  const query = this.value.toLowerCase();
  const rows = document.querySelectorAll('#user-table-body tr');
  
  rows.forEach(row => {
    const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
    const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
    
    if (username.includes(query) || email.includes(query)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});

// Search functionality for posts
document.getElementById('search-post').addEventListener('keyup', function(e) {
  const query = this.value.toLowerCase();
  const rows = document.querySelectorAll('#post-table-body tr');
  
  rows.forEach(row => {
    const caption = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
    const author = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
    
    if (caption.includes(query) || author.includes(query)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});

// Toggle pin status for posts
function togglePin(postId, element) {
  const formData = new FormData();
  formData.append('post_id', postId);
  
  fetch('../../service/api/pin_post.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Update the status badge
      const statusCell = element.closest('tr').querySelector('td:nth-child(6)');
      if (data.is_pinned) {
        statusCell.innerHTML = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-900/30 text-yellow-300"><i class="bi bi-pin-fill"></i> Pinned</span>';
        element.title = 'Unpin';
      } else {
        statusCell.innerHTML = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-700/30 text-gray-300">Normal</span>';
        element.title = 'Toggle Pin';
      }
      element.classList.toggle('active');
    } else {
      alert('Gagal mengubah status pin');
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Terjadi kesalahan');
  });
}

// Make tab buttons clickable with keyboard support
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      this.click();
    }
  });
});
</script>

</body>
</html>
