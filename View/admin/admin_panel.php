<?php
session_start();
include '../../service/db.php';

// Path yang benar untuk password_hash.php
// Jika file ini ada di folder yang sama, gunakan include 'password_hash.php'
// Jika di folder lain, sesuaikan path-nya
// include 'password_hash.php'; 

if(
    !isset($_SESSION['admin_id']) || 
    !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')
) {
    header("Location: admin_login.php");
    exit();
}

// Proses reset password user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'reset_user_password') {
        $user_id = $_POST['user_id'] ?? 0;
        $new_password = $_POST['new_password'] ?? '';
        
        if (empty($user_id) || empty($new_password)) {
            echo json_encode([
                'success' => false,
                'message' => 'ID user dan password baru harus diisi'
            ]);
            exit;
        }
        
        // Validasi password
        $errors = [];
        if (strlen($new_password) < 8) {
            $errors[] = "Password minimal 8 karakter";
        }
        if (!preg_match('/[A-Z]/', $new_password)) {
            $errors[] = "Password harus mengandung huruf besar (A-Z)";
        }
        if (!preg_match('/[a-z]/', $new_password)) {
            $errors[] = "Password harus mengandung huruf kecil (a-z)";
        }
        if (!preg_match('/[0-9]/', $new_password)) {
            $errors[] = "Password harus mengandung angka (0-9)";
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $new_password)) {
            $errors[] = "Password harus mengandung karakter spesial (!@#$%^&* dll)";
        }
        
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
            exit;
        }
        
        // Hash password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Update password di database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            // Log perubahan password
            $admin_id = $_SESSION['admin_id'];
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            // Buat tabel password_history jika belum ada
            $conn->query("
                CREATE TABLE IF NOT EXISTS password_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    changed_by VARCHAR(255),
                    ip_address VARCHAR(45),
                    user_agent VARCHAR(500),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            
            // Ambil username user yang password-nya di-reset
            $user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user_data = $user_result->fetch_assoc();
            $username = $user_data['username'] ?? 'Unknown';
            
            // Ambil username admin yang mereset password
            $admin_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $admin_stmt->bind_param("i", $admin_id);
            $admin_stmt->execute();
            $admin_result = $admin_stmt->get_result();
            $admin_data = $admin_result->fetch_assoc();
            $admin_username = $admin_data['username'] ?? 'Unknown';
            
            // Insert ke password_history
            $log_stmt = $conn->prepare("
                INSERT INTO password_history (user_id, changed_by, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)
            ");
            $log_stmt->bind_param("isss", $user_id, $admin_username, $ip_address, $user_agent);
            $log_stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => "Password user {$username} berhasil direset"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mereset password: ' . $stmt->error
            ]);
        }
        exit;
    }
}

 $admin_id = $_SESSION['admin_id'];
 $admin_stmt = $conn->prepare("SELECT username, role, email FROM users WHERE id = ?");
 $admin_stmt->bind_param("i", $admin_id);
 $admin_stmt->execute();
 $admin = $admin_stmt->get_result()->fetch_assoc();
 $admin_stmt->close();

 $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
 $total_posts = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
 $total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
 $pinned_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE is_pinned = 1")->fetch_assoc()['count'];

 $users = $conn->query("SELECT id, username, email, role, title, level FROM users ORDER BY id DESC");
 $posts = $conn->query("SELECT p.id, p.caption, p.is_pinned, u.username, (SELECT COUNT(*) FROM comments WHERE post_id=p.id) as comment_count, (SELECT COUNT(*) FROM post_likes WHERE post_id=p.id) as like_count, p.created_at FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 50");

 $user_registrations = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");

 $posts_by_month = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM posts 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");

 $comments_by_month = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM comments 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");

 $user_roles = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");

 $active_users = $conn->query("
    SELECT u.username, COUNT(p.id) as post_count
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
    GROUP BY u.id
    ORDER BY post_count DESC
    LIMIT 5
");

 $liked_posts = $conn->query("
    SELECT p.id, p.caption, COUNT(pl.id) as like_count, u.username
    FROM posts p
    JOIN post_likes pl ON p.id = pl.post_id
    JOIN users u ON p.user_id = u.id
    GROUP BY p.id
    ORDER BY like_count DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  * { box-sizing: border-box; }
  body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
  .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; }
  .gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
  .sidebar { width: 260px; position: relative; z-index: 10; transition: all 0.3s ease; }
  .main-content { flex: 1; overflow-y: auto; }
  .stat-card { transition: all 0.3s ease; }
  .stat-card:hover { transform: translateY(-5px); }
  .nav-item { transition: all 0.2s ease; border-left: 3px solid transparent; cursor: pointer; }
  .nav-item.active { background: rgba(239, 68, 68, 0.2); border-left: 3px solid #ef4444; }
  .nav-item:hover { background: rgba(255,255,255,0.05); }
  .tab-content { animation: fadeIn 0.3s ease; }
  .tab-content.hidden { display: none !important; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
  table tbody tr { transition: all 0.2s ease; }
  table tbody tr:hover { background: rgba(255,255,255,0.1); }
  .chart-container { position: relative; height: 300px; margin: 20px 0; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: center; gap: 12px; }
  .alert-success { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; }
  .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; }
  .form-group { margin-bottom: 16px; }
  .form-group label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; color: #d1d5db; }
  .form-group input, .form-group select { width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 14px; transition: all 0.3s ease; }
  .form-group select option { background: rgba(30, 30, 30, 0.95); color: #d1d5db; padding: 8px; }
  .form-group input:focus, .form-group select:focus { outline: none; border-color: #ef4444; background: rgba(255,255,255,0.08); }
  .btn { padding: 10px 16px; border-radius: 8px; border: none; font-weight: 500; cursor: pointer; transition: all 0.3s ease; font-size: 14px; }
  .btn-primary { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3); }
  .btn-secondary { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); }
  .btn-secondary:hover { background: rgba(255,255,255,0.15); }
  @media (max-width: 768px) {
    .sidebar { width: 100%; position: fixed; bottom: 0; left: 0; right: 0; height: auto; border-right: none; border-top: 1px solid rgba(255,255,255,0.1); max-height: 60px; }
    .sidebar > div:first-child { display: none; }
    nav.flex-1 { flex: 1 !important; padding: 5px 10px !important; flex-direction: row !important; }
    nav.flex-1 button { flex: 1; padding: 8px 12px !important; font-size: 12px !important; }
    .sidebar > div:last-child { display: none; }
    .main-content { padding-bottom: 80px; }
  }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">

<div class="flex h-screen">
  <div class="sidebar glass border-r border-white/10 flex flex-col">
    <div class="p-6 border-b border-white/10">
      <div class="text-xl font-bold">
        <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
      </div>
      <p class="text-xs text-gray-500 mt-1">Admin Panel</p>
    </div>

    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
      <button onclick="switchTab('dashboard')" class="tab-btn active nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm" data-tab="dashboard">
        <i class="bi bi-speedometer2 text-lg"></i><span>Dashboard</span>
      </button>
      <button onclick="switchTab('users')" class="tab-btn nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm" data-tab="users">
        <i class="bi bi-people text-lg"></i><span>Kelola User</span>
      </button>
      <button onclick="switchTab('posts')" class="tab-btn nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm" data-tab="posts">
        <i class="bi bi-file-text text-lg"></i><span>Kelola Post</span>
      </button>
      <button onclick="switchTab('password')" class="tab-btn nav-item w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm" data-tab="password">
        <i class="bi bi-lock text-lg"></i><span>Reset Password</span>
      </button>
    </nav>

    <div class="p-4 border-t border-white/10 space-y-3">
      <div class="glass p-3 rounded-lg text-xs">
        <p class="text-gray-400">Admin</p>
        <p class="font-semibold text-sm"><?= htmlspecialchars($admin['username']) ?></p>
        <p class="text-gray-500 text-xs mt-1"><?= ucfirst($admin['role']) ?></p>
      </div>
      <a href="logout.php" class="glass hover:bg-red-600/20 w-full text-center py-2 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-2">
        <i class="bi bi-box-arrow-right"></i><span>Logout</span>
      </a>
    </div>
  </div>

  <div class="main-content overflow-y-auto">
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

    <div class="p-8">
      <div id="tab-dashboard" class="tab-content">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div><p class="text-gray-400 text-sm">Total User</p><h3 class="text-3xl font-bold mt-2"><?= $total_users ?></h3></div>
              <i class="bi bi-people text-4xl text-blue-500 opacity-20"></i>
            </div>
          </div>
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div><p class="text-gray-400 text-sm">Total Post</p><h3 class="text-3xl font-bold mt-2"><?= $total_posts ?></h3></div>
              <i class="bi bi-file-text text-4xl text-green-500 opacity-20"></i>
            </div>
          </div>
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div><p class="text-gray-400 text-sm">Total Komentar</p><h3 class="text-3xl font-bold mt-2"><?= $total_comments ?></h3></div>
              <i class="bi bi-chat-dots text-4xl text-purple-500 opacity-20"></i>
            </div>
          </div>
          <div class="stat-card glass p-6 rounded-2xl">
            <div class="flex items-center justify-between">
              <div><p class="text-gray-400 text-sm">Post Ter-pin</p><h3 class="text-3xl font-bold mt-2"><?= $pinned_posts ?></h3></div>
              <i class="bi bi-pin-fill text-4xl text-yellow-500 opacity-20"></i>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div class="glass p-6 rounded-2xl"><h3 class="text-xl font-semibold mb-4">Pertumbuhan Pengguna</h3><div class="chart-container"><canvas id="userRegistrationChart"></canvas></div></div>
          <div class="glass p-6 rounded-2xl"><h3 class="text-xl font-semibold mb-4">Aktivitas Konten</h3><div class="chart-container"><canvas id="postsCommentsChart"></canvas></div></div>
          <div class="glass p-6 rounded-2xl"><h3 class="text-xl font-semibold mb-4">Distribusi Peran Pengguna</h3><div class="chart-container"><canvas id="userRolesChart"></canvas></div></div>
          <div class="glass p-6 rounded-2xl"><h3 class="text-xl font-semibold mb-4">Pengguna Paling Aktif</h3><div class="chart-container"><canvas id="activeUsersChart"></canvas></div></div>
        </div>

        <div class="glass p-6 rounded-2xl">
          <h3 class="text-xl font-semibold mb-4">Post Paling Disukai</h3>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead><tr class="border-b border-white/10"><th class="text-left py-3 px-4 text-gray-400">ID</th><th class="text-left py-3 px-4 text-gray-400">Caption</th><th class="text-left py-3 px-4 text-gray-400">Author</th><th class="text-left py-3 px-4 text-gray-400">Likes</th></tr></thead>
              <tbody>
                <?php while($post = $liked_posts->fetch_assoc()): ?>
                <tr class="border-b border-white/10 hover:bg-white/5 transition">
                  <td class="py-3 px-4"><?= $post['id'] ?></td>
                  <td class="py-3 px-4 font-medium max-w-xs truncate"><?= htmlspecialchars(substr($post['caption'], 0, 50)) ?>...</td>
                  <td class="py-3 px-4"><?= htmlspecialchars($post['username']) ?></td>
                  <td class="py-3 px-4 text-center"><span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-900/30 text-red-300"><i class="bi bi-heart-fill"></i> <?= $post['like_count'] ?></span></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="tab-users" class="tab-content hidden glass p-6 rounded-2xl">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Kelola User</h2>
          <input type="text" id="search-user" placeholder="Cari user..." class="glass px-4 py-2 rounded-lg text-sm border border-white/10 focus:border-red-500 outline-none transition w-64">
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead><tr class="border-b border-white/10"><th class="text-left py-3 px-4 text-gray-400">ID</th><th class="text-left py-3 px-4 text-gray-400">Username</th><th class="text-left py-3 px-4 text-gray-400">Email</th><th class="text-left py-3 px-4 text-gray-400">Role</th><th class="text-left py-3 px-4 text-gray-400">Title</th><th class="text-left py-3 px-4 text-gray-400">Level</th><th class="text-left py-3 px-4 text-gray-400">Aksi</th></tr></thead>
            <tbody id="user-table-body">
              <?php $users->data_seek(0); while($user = $users->fetch_assoc()): ?>
              <tr class="border-b border-white/10 hover:bg-white/5 transition">
                <td class="py-3 px-4"><?= $user['id'] ?></td>
                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($user['username']) ?></td>
                <td class="py-3 px-4 text-gray-400 text-xs"><?= htmlspecialchars($user['email']) ?></td>
                <td class="py-3 px-4"><span class="px-2 py-1 rounded-full text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-red-900/30 text-red-300' : ($user['role'] === 'developer' ? 'bg-blue-900/30 text-blue-300' : 'bg-gray-700/30 text-gray-300') ?>"><?= ucfirst($user['role']) ?></span></td>
                <td class="py-3 px-4"><?= htmlspecialchars($user['title']) ?></td>
                <td class="py-3 px-4 font-semibold"><?= $user['level'] ?></td>
                <td class="py-3 px-4"><a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-400 hover:text-blue-300 text-xs font-semibold mr-3"><i class="bi bi-pencil-square"></i> Edit</a><a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="text-red-400 hover:text-red-300 text-xs font-semibold"><i class="bi bi-trash"></i> Hapus</a></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="tab-posts" class="tab-content hidden glass p-6 rounded-2xl">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Kelola Post</h2>
          <input type="text" id="search-post" placeholder="Cari post..." class="glass px-4 py-2 rounded-lg text-sm border border-white/10 focus:border-green-500 outline-none transition w-64">
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead><tr class="border-b border-white/10"><th class="text-left py-3 px-4 text-gray-400">ID</th><th class="text-left py-3 px-4 text-gray-400">Caption</th><th class="text-left py-3 px-4 text-gray-400">Author</th><th class="text-left py-3 px-4 text-gray-400">Komentar</th><th class="text-left py-3 px-4 text-gray-400">Like</th><th class="text-left py-3 px-4 text-gray-400">Status</th><th class="text-left py-3 px-4 text-gray-400">Aksi</th></tr></thead>
            <tbody id="post-table-body">
              <?php $posts->data_seek(0); while($post = $posts->fetch_assoc()): ?>
              <tr class="border-b border-white/10 hover:bg-white/5 transition">
                <td class="py-3 px-4"><?= $post['id'] ?></td>
                <td class="py-3 px-4 font-medium max-w-xs truncate"><?= htmlspecialchars(substr($post['caption'], 0, 50)) ?>...</td>
                <td class="py-3 px-4"><?= htmlspecialchars($post['username']) ?></td>
                <td class="py-3 px-4 text-center"><?= $post['comment_count'] ?></td>
                <td class="py-3 px-4 text-center"><?= $post['like_count'] ?></td>
                <td class="py-3 px-4"><?php if($post['is_pinned']): ?><span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-900/30 text-yellow-300"><i class="bi bi-pin-fill"></i> Pinned</span><?php else: ?><span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-700/30 text-gray-300">Normal</span><?php endif; ?></td>
                <td class="py-3 px-4"><button onclick="togglePin(<?= $post['id'] ?>, this)" class="text-yellow-400 hover:text-yellow-300 text-xs font-semibold mr-3" title="Toggle Pin"><i class="bi bi-pin"></i></button><a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Yakin ingin menghapus post ini?')" class="text-red-400 hover:text-red-300 text-xs font-semibold"><i class="bi bi-trash"></i> Hapus</a></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="tab-password" class="tab-content hidden">
        <div class="glass p-6 rounded-2xl max-w-2xl">
          <h2 class="text-2xl font-bold mb-6"><i class="bi bi-lock"></i> Reset Password User</h2>
          <div id="reset-password-alert"></div>
          <form id="reset-password-form">
            <div class="form-group">
              <label>Cari User (Username/Email)</label>
              <input type="text" id="user_search" placeholder="Ketik username atau email user..." class="glass px-4 py-2 rounded-lg text-sm border border-white/10 focus:border-red-500 outline-none transition w-full">
            </div>
            <div class="form-group">
              <label>Pilih User</label>
              <select id="user_id" name="user_id" required style="color: #9ca3af;">
                <option value="" style="color: #9ca3af;">-- Pilih User --</option>
                <?php $users->data_seek(0); while($user = $users->fetch_assoc()): ?>
                  <option value="<?= $user['id'] ?>" style="color: #ffffff;"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Password Baru</label>
              <div style="display: flex; gap: 8px;">
                <input type="text" id="generated_password" readonly placeholder="Password akan di-generate" style="flex: 1;">
                <button type="button" onclick="generatePassword()" class="btn btn-secondary" style="flex-shrink: 0;">
                  <i class="bi bi-arrow-repeat"></i> Generate
                </button>
              </div>
            </div>
            <div class="form-group">
              <label><input type="checkbox" id="custom_password_check" onchange="toggleCustomPassword()"> Gunakan Password Custom</label>
              <input type="password" id="custom_password" placeholder="Masukkan password custom" style="display: none; margin-top: 8px;">
            </div>
            <button type="submit" class="btn btn-primary w-full mt-6">
              <i class="bi bi-lock-fill"></i> Reset Password User
            </button>
          </form>
        </div>

        <div class="glass p-6 rounded-2xl mt-6">
          <h2 class="text-2xl font-bold mb-6"><i class="bi bi-clock-history"></i> Riwayat Reset Password</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead><tr class="border-b border-white/10"><th class="text-left py-3 px-4 text-gray-400">User</th><th class="text-left py-3 px-4 text-gray-400">Waktu Reset</th><th class="text-left py-3 px-4 text-gray-400">Di-reset Oleh</th><th class="text-left py-3 px-4 text-gray-400">IP Address</th></tr></thead>
              <tbody id="password-history-body">
                <tr><td colspan="4" class="py-4 px-4 text-center text-gray-500">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function generatePassword() {
  const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
  let password = '';
  for (let i = 0; i < 16; i++) {
    password += charset.charAt(Math.floor(Math.random() * charset.length));
  }
  document.getElementById('generated_password').value = password;
}

function toggleCustomPassword() {
  const customCheck = document.getElementById('custom_password_check').checked;
  const customInput = document.getElementById('custom_password');
  const generatedInput = document.getElementById('generated_password');
  customInput.style.display = customCheck ? 'block' : 'none';
  generatedInput.parentElement.style.display = customCheck ? 'none' : 'flex';
}

function showAlert(elementId, message, type) {
  const alertEl = document.getElementById(elementId);
  alertEl.innerHTML = `<div class="alert alert-${type}"><i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'}"></i><span>${message}</span></div>`;
  setTimeout(() => { if (alertEl) alertEl.innerHTML = ''; }, 5000);
}

function loadPasswordHistory() {
  fetch('get_password_history.php')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('password-history-body');
      if (data.success && data.history.length > 0) {
        tbody.innerHTML = data.history.map(h => `<tr class="border-b border-white/10 hover:bg-white/5"><td class="py-3 px-4">${h.username}</td><td class="py-3 px-4">${new Date(h.changed_at).toLocaleString('id-ID')}</td><td class="py-3 px-4">${h.changed_by || '-'}</td><td class="py-3 px-4 text-xs">${h.ip_address}</td></tr>`).join('');
      } else {
        tbody.innerHTML = '<tr><td colspan="4" class="py-4 px-4 text-center text-gray-500">Belum ada riwayat</td></tr>';
      }
    })
    .catch(err => {
      console.error('Error loading password history:', err);
      document.getElementById('password-history-body').innerHTML = '<tr><td colspan="4" class="py-4 px-4 text-center text-gray-500">Gagal memuat riwayat</td></tr>';
    });
}

function updateDateTime() {
  const now = new Date();
  const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  document.getElementById('current-date').textContent = dateStr;
  document.getElementById('current-time').textContent = timeStr;
}

function switchTab(tabName) {
  document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
  const selectedTab = document.getElementById(`tab-${tabName}`);
  if (selectedTab) {
    selectedTab.classList.remove('hidden');
    if (tabName === 'password') loadPasswordHistory();
  }
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
  if (activeBtn) activeBtn.classList.add('active');
}

document.getElementById('user_search')?.addEventListener('keyup', function(e) {
  const query = this.value.toLowerCase();
  const select = document.getElementById('user_id');
  const options = select.querySelectorAll('option');
  options.forEach((option, index) => {
    if (index === 0) return;
    const text = option.textContent.toLowerCase();
    option.style.display = text.includes(query) ? '' : 'none';
  });
});

document.getElementById('reset-password-form')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const userId = document.getElementById('user_id').value;
  const customCheck = document.getElementById('custom_password_check').checked;
  const password = customCheck ? document.getElementById('custom_password').value : document.getElementById('generated_password').value;
  
  if (!userId) {
    showAlert('reset-password-alert', 'Pilih user terlebih dahulu', 'error');
    return;
  }
  if (!password) {
    showAlert('reset-password-alert', 'Generate atau masukkan password', 'error');
    return;
  }
  
  const formData = new FormData();
  formData.append('action', 'reset_user_password');
  formData.append('user_id', userId);
  formData.append('new_password', password);
  
  // Menggunakan endpoint yang sama dengan file ini
  fetch(window.location.href, { method: 'POST', body: formData })
    .then(res => {
      if (!res.ok) {
        throw new Error('Server error');
      }
      return res.json();
    })
    .then(data => {
      if (data.success) {
        showAlert('reset-password-alert', data.message, 'success');
        document.getElementById('reset-password-form').reset();
        document.getElementById('generated_password').value = '';
        loadPasswordHistory();
      } else {
        showAlert('reset-password-alert', data.message, 'error');
      }
    })
    .catch(err => {
      console.error('Error:', err);
      showAlert('reset-password-alert', 'Terjadi kesalahan: ' + err.message, 'error');
    });
});

document.getElementById('search-user')?.addEventListener('keyup', function(e) {
  const query = this.value.toLowerCase();
  const rows = document.querySelectorAll('#user-table-body tr');
  rows.forEach(row => {
    const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
    const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
    row.style.display = (username.includes(query) || email.includes(query)) ? '' : 'none';
  });
});

document.getElementById('search-post')?.addEventListener('keyup', function(e) {
  const query = this.value.toLowerCase();
  const rows = document.querySelectorAll('#post-table-body tr');
  rows.forEach(row => {
    const caption = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
    const author = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
    row.style.display = (caption.includes(query) || author.includes(query)) ? '' : 'none';
  });
});

function togglePin(postId, element) {
  const formData = new FormData();
  formData.append('post_id', postId);
  fetch('../../service/api/pin_post.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const statusCell = element.closest('tr').querySelector('td:nth-child(6)');
        if (data.is_pinned) {
          statusCell.innerHTML = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-900/30 text-yellow-300"><i class="bi bi-pin-fill"></i> Pinned</span>';
          element.title = 'Unpin';
        } else {
          statusCell.innerHTML = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-700/30 text-gray-300">Normal</span>';
          element.title = 'Toggle Pin';
        }
      } else {
        alert('Gagal mengubah status pin: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => alert('Terjadi kesalahan saat menghubungi server'));
}

updateDateTime();
setInterval(updateDateTime, 1000);

document.addEventListener('DOMContentLoaded', function() {
  Chart.defaults.color = '#9ca3af';
  Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
  
  const userRegCtx = document.getElementById('userRegistrationChart')?.getContext('2d');
  if (userRegCtx) {
    new Chart(userRegCtx, {
      type: 'line',
      data: {
        labels: [<?php $user_registrations->data_seek(0); while($row = $user_registrations->fetch_assoc()) { echo "'" . DateTime::createFromFormat('Y-m', $row['month'])->format('M Y') . "',"; } ?>],
        datasets: [{
          label: 'Pengguna Baru',
          data: [<?php $user_registrations->data_seek(0); while($row = $user_registrations->fetch_assoc()) { echo $row['count'] . ","; } ?>],
          backgroundColor: 'rgba(59, 130, 246, 0.2)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 2,
          tension: 0.4,
          fill: true
        }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } }, x: { grid: { color: 'rgba(255, 255, 255, 0.05)' } } } }
    });
  }
  
  const postsCommentsCtx = document.getElementById('postsCommentsChart')?.getContext('2d');
  if (postsCommentsCtx) {
    const postsByMonth = [<?php $posts_by_month->data_seek(0); while($row = $posts_by_month->fetch_assoc()) { echo $row['count'] . ","; } ?>];
    const commentsByMonth = [<?php $comments_by_month->data_seek(0); while($row = $comments_by_month->fetch_assoc()) { echo $row['count'] . ","; } ?>];
    const months = [<?php $posts_by_month->data_seek(0); while($row = $posts_by_month->fetch_assoc()) { echo "'" . DateTime::createFromFormat('Y-m', $row['month'])->format('M Y') . "',"; } ?>];
    new Chart(postsCommentsCtx, {
      type: 'bar',
      data: { labels: months, datasets: [ { label: 'Posts', data: postsByMonth, backgroundColor: 'rgba(34, 197, 94, 0.7)' }, { label: 'Comments', data: commentsByMonth, backgroundColor: 'rgba(139, 92, 246, 0.7)' } ] },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } }, x: { grid: { color: 'rgba(255, 255, 255, 0.05)' } } } }
    });
  }
  
  const userRolesCtx = document.getElementById('userRolesChart')?.getContext('2d');
  if (userRolesCtx) {
    new Chart(userRolesCtx, {
      type: 'doughnut',
      data: { labels: [<?php $user_roles->data_seek(0); while($row = $user_roles->fetch_assoc()) { echo "'" . ucfirst($row['role']) . "',"; } ?>], datasets: [{ data: [<?php $user_roles->data_seek(0); while($row = $user_roles->fetch_assoc()) { echo $row['count'] . ","; } ?>], backgroundColor: ['rgba(239, 68, 68, 0.7)', 'rgba(59, 130, 246, 0.7)', 'rgba(107, 114, 128, 0.7)'] }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
  }
  
  const activeUsersCtx = document.getElementById('activeUsersChart')?.getContext('2d');
  if (activeUsersCtx) {
    new Chart(activeUsersCtx, {
      type: 'bar',
      data: { labels: [<?php $active_users->data_seek(0); while($row = $active_users->fetch_assoc()) { echo "'" . htmlspecialchars($row['username']) . "',"; } ?>], datasets: [{ label: 'Jumlah Post', data: [<?php $active_users->data_seek(0); while($row = $active_users->fetch_assoc()) { echo $row['post_count'] . ","; } ?>], backgroundColor: 'rgba(251, 191, 36, 0.7)' }] },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } }, x: { grid: { color: 'rgba(255, 255, 255, 0.05)' } } } }
    });
  }
});
</script>
</body>
</html>