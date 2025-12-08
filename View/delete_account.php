<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['confirm_delete'])) {
    $password = $_POST['password'];
    
    // Verifikasi password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if(password_verify($password, $user['password'])) {
        // Hapus semua gambar post user ini
        $posts = $conn->query("SELECT image FROM posts WHERE user_id = $user_id AND image IS NOT NULL");
        while($post = $posts->fetch_assoc()) {
            if(file_exists("../uploads/" . $post['image'])) {
                unlink("../uploads/" . $post['image']);
            }
        }
        
        // Hapus user (foreign key akan auto hapus posts, comments, likes, notifications)
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Logout
        session_destroy();
        header("Location: ../../index.php?account_deleted=1");
        exit;
    } else {
        header("Location: settings.php?error=wrong_password");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delete Account - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  body { font-family: 'Poppins', sans-serif; }
  .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen flex items-center justify-center p-4">

<div class="glass rounded-3xl p-8 max-w-md w-full">
  <div class="text-center mb-6">
    <i class="bi bi-exclamation-triangle text-6xl text-red-500 mb-4"></i>
    <h1 class="text-3xl font-bold mb-2">Hapus Akun</h1>
    <p class="text-gray-400">Tindakan ini tidak dapat dibatalkan!</p>
  </div>

  <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6">
    <h3 class="font-bold mb-2 flex items-center gap-2">
      <i class="bi bi-info-circle"></i>
      Data yang akan dihapus:
    </h3>
    <ul class="text-sm text-gray-300 space-y-1">
      <li>• Semua post dan gambar Anda</li>
      <li>• Semua komentar Anda</li>
      <li>• Semua like Anda</li>
      <li>• Notifikasi terkait akun Anda</li>
      <li>• Data profil dan statistik</li>
    </ul>
  </div>

  <form method="post" action="">
    <div class="mb-4">
      <label class="block text-sm font-semibold mb-2">Konfirmasi dengan Password:</label>
      <input type="password" name="password" required
        class="w-full p-3 rounded-xl bg-white/10 border border-white/20 focus:outline-none focus:border-red-500 transition"
        placeholder="Masukkan password Anda">
    </div>

    <div class="flex gap-3">
      <a href="halaman_php.php" class="flex-1 bg-gray-600 hover:bg-gray-700 text-center py-3 rounded-xl font-semibold transition">
        Batal
      </a>
      <button type="submit" name="confirm_delete"
        class="flex-1 bg-red-600 hover:bg-red-700 py-3 rounded-xl font-semibold transition">
        Hapus Akun
      </button>
    </div>
  </form>
</div>

</body>
</html>