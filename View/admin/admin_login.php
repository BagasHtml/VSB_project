<?php
session_start();
if(isset($_POST['login'])) {
    include '../../service/db.php';

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin' || $user['role'] === 'developer') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                header("Location: admin_panel.php");
                exit();
            } else {
                header("Location: admin_login.php?error=" . urlencode("Akses ditolak! Kamu bukan admin."));
                exit();
            }
        } else {
            header("Location: admin_login.php?error=" . urlencode("Password salah."));
            exit();
        }
    } else {
        header("Location: admin_login.php?error=" . urlencode("Email tidak ditemukan."));
        exit();
    }
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$logout = isset($_GET['logout']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  body { font-family: 'Poppins', sans-serif; }
  .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
  .gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
  .glow-effect { box-shadow: 0 0 30px rgba(239, 68, 68, 0.3); }
  input::placeholder { opacity: 0; }
  input:focus::placeholder { opacity: 1; }
  input:disabled { opacity: 0.5; cursor: not-allowed; }
  .input-icon { transition: all 0.3s ease; }
  input:focus ~ .input-icon { color: #ef4444; }
  @keyframes slideInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-in { animation: slideInDown 0.6s ease; }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen flex items-center justify-center p-4">

<!-- Background decorations -->
<div class="fixed inset-0 overflow-hidden pointer-events-none">
  <div class="absolute top-10 right-10 w-96 h-96 bg-red-500/10 rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 left-10 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
</div>

<!-- Main container -->
<div class="relative z-10 w-full max-w-md">
  <!-- Header section -->
  <div class="text-center mb-8 animate-in">
    <div class="inline-block mb-4">
      <div class="w-16 h-16 glass glow-effect rounded-full flex items-center justify-center">
        <i class="bi bi-shield-lock text-3xl text-red-400"></i>
      </div>
    </div>
    <h1 class="text-4xl font-bold mb-2">
      <span class="text-gray-100">Knowledge</span><span class="gradient-text">Battle</span>
    </h1>
    <p class="text-gray-400 text-sm">Admin Panel Access</p>
  </div>

  <!-- Login card -->
  <div class="glass glow-effect rounded-3xl p-8 mb-6 animate-in" style="animation-delay: 0.1s;">
    <!-- Success message -->
    <?php if($logout): ?>
      <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-xl flex items-start gap-3">
        <i class="bi bi-check-circle text-green-400 text-xl mt-0.5"></i>
        <div>
          <p class="font-semibold text-sm">Logout Berhasil</p>
          <p class="text-xs text-gray-300 mt-1">Anda telah logout dari admin panel</p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Error message -->
    <?php if($error): ?>
      <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-xl flex items-start gap-3">
        <i class="bi bi-exclamation-circle text-red-400 text-xl mt-0.5"></i>
        <div>
          <p class="font-semibold text-sm">Login Gagal</p>
          <p class="text-xs text-gray-300 mt-1"><?= $error ?></p>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="space-y-4">
      <!-- Email field -->
      <div class="relative">
        <input type="email" name="email" required 
          class="w-full bg-gray-800/50 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500/50 focus:bg-gray-800 transition peer"
          placeholder="Email">
        <label class="absolute left-4 -top-2.5 text-xs text-gray-400 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-2 peer-focus:text-red-400 peer-focus:text-xs transition">
          <i class="bi bi-envelope mr-1"></i> Email Address
        </label>
      </div>

      <!-- Password field -->
      <div class="relative">
        <input type="password" name="password" required 
          class="w-full bg-gray-800/50 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500/50 focus:bg-gray-800 transition peer"
          placeholder="Password">
        <label class="absolute left-4 -top-2.5 text-xs text-gray-400 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-2 peer-focus:text-red-400 peer-focus:text-xs transition">
          <i class="bi bi-lock mr-1"></i> Password
        </label>
      </div>

      <!-- Remember me checkbox -->
      <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition">
        <input type="checkbox" class="w-4 h-4 rounded border-white/10 bg-gray-800 cursor-pointer">
        <span>Ingat saya di device ini</span>
      </label>

      <!-- Login button -->
      <button type="submit" name="login" 
        class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 py-3 rounded-xl font-semibold transition transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 mt-6">
        <i class="bi bi-arrow-right-circle"></i>
        <span>Masuk ke Admin Panel</span>
      </button>

      <!-- Divider -->
      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-white/10"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-2 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-500">atau</span>
        </div>
      </div>

      <!-- Back button -->
      <a href="../../View/halaman_utama.php" 
        class="w-full glass hover:bg-white/10 py-3 rounded-xl font-semibold transition text-center flex items-center justify-center gap-2">
        <i class="bi bi-arrow-left"></i>
        <span>Kembali ke Forum</span>
      </a>
    </form>
  </div>

  <!-- Footer info -->
  <div class="glass rounded-2xl p-4 text-center text-xs text-gray-400">
    <p class="mb-2">Hanya untuk admin dan developer</p>
    <p>Role: <span class="text-red-400 font-semibold">Admin</span> atau <span class="text-blue-400 font-semibold">Developer</span></p>
  </div>
</div>

<script>
  // Form validation
  const form = document.querySelector('form');
  form.addEventListener('submit', function(e) {
    const email = document.querySelector('input[type="email"]');
    const password = document.querySelector('input[type="password"]');
    
    if (!email.value.trim()) {
      e.preventDefault();
      email.focus();
      email.classList.add('border-red-500');
      return;
    }
    
    if (!password.value.trim()) {
      e.preventDefault();
      password.focus();
      password.classList.add('border-red-500');
      return;
    }
  });

  // Remove error class on input
  document.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', function() {
      this.classList.remove('border-red-500');
    });
  });

  // Focus effect
  document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('ring-1', 'ring-red-500/50');
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('ring-1', 'ring-red-500/50');
    });
  });
</script>

</body>
</html>