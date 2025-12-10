<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register/form_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle password change
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif(strlen($new_password) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } elseif($new_password !== $confirm_password) {
        $error = 'Password baru dan konfirmasi tidak cocok!';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if(password_verify($current_password, $result['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $user_id);
            
            if($update->execute()) {
                $success = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password. Silakan coba lagi.';
            }
        } else {
            $error = 'Password lama salah!';
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ganti Password - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  body{font-family:'Poppins',sans-serif;}
  .glass{background:rgba(255,255,255,0.05);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1);}
  .gradient-text{background:linear-gradient(to right,#ef4444,#dc2626);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
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
<main class="pt-28 pb-12 max-w-2xl mx-auto px-4">

  <!-- SUCCESS MESSAGE -->
  <?php if($success): ?>
  <div class="glass p-4 rounded-xl mb-6 border border-green-500/50 bg-green-500/10">
    <div class="flex items-center gap-3">
      <i class="bi bi-check-circle text-2xl text-green-400"></i>
      <p class="text-green-400"><?= $success ?></p>
    </div>
  </div>
  <?php endif; ?>

  <!-- ERROR MESSAGE -->
  <?php if($error): ?>
  <div class="glass p-4 rounded-xl mb-6 border border-red-500/50 bg-red-500/10">
    <div class="flex items-center gap-3">
      <i class="bi bi-exclamation-circle text-2xl text-red-400"></i>
      <p class="text-red-400"><?= $error ?></p>
    </div>
  </div>
  <?php endif; ?>

  <!-- CHANGE PASSWORD FORM -->
  <div class="glass p-8 rounded-3xl">
    <div class="flex items-center gap-4 mb-8">
      <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center">
        <i class="bi bi-key text-3xl"></i>
      </div>
      <div>
        <h1 class="text-2xl font-bold">Ganti Password</h1>
        <p class="text-gray-400">Update password akun Anda</p>
      </div>
    </div>

    <form method="post" class="space-y-6">
      <!-- Current Password -->
      <div>
        <label class="block text-sm font-semibold mb-2">Password Lama</label>
        <div class="relative">
          <input type="password" name="current_password" id="current_password" required
            class="w-full glass p-4 rounded-xl focus:outline-none focus:border-green-500 border border-white/10 transition pr-12">
          <button type="button" onclick="togglePassword('current_password', 'current_icon')" 
            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition">
            <i class="bi bi-eye" id="current_icon"></i>
          </button>
        </div>
      </div>

      <!-- New Password -->
      <div>
        <label class="block text-sm font-semibold mb-2">Password Baru</label>
        <div class="relative">
          <input type="password" name="new_password" id="new_password" required minlength="6"
            class="w-full glass p-4 rounded-xl focus:outline-none focus:border-green-500 border border-white/10 transition pr-12">
          <button type="button" onclick="togglePassword('new_password', 'new_icon')" 
            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition">
            <i class="bi bi-eye" id="new_icon"></i>
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Minimal 6 karakter</p>
      </div>

      <!-- Confirm Password -->
      <div>
        <label class="block text-sm font-semibold mb-2">Konfirmasi Password Baru</label>
        <div class="relative">
          <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
            class="w-full glass p-4 rounded-xl focus:outline-none focus:border-green-500 border border-white/10 transition pr-12">
          <button type="button" onclick="togglePassword('confirm_password', 'confirm_icon')" 
            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition">
            <i class="bi bi-eye" id="confirm_icon"></i>
          </button>
        </div>
      </div>

      <!-- Password Strength Indicator -->
      <div id="password-strength" class="hidden">
        <div class="flex gap-2 mb-2">
          <div class="h-2 flex-1 rounded-full bg-gray-700" id="strength-bar-1"></div>
          <div class="h-2 flex-1 rounded-full bg-gray-700" id="strength-bar-2"></div>
          <div class="h-2 flex-1 rounded-full bg-gray-700" id="strength-bar-3"></div>
          <div class="h-2 flex-1 rounded-full bg-gray-700" id="strength-bar-4"></div>
        </div>
        <p class="text-sm" id="strength-text">Password strength</p>
      </div>

      <!-- Submit Button -->
      <button type="submit" name="change_password" 
        class="w-full bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 px-6 py-4 rounded-xl font-semibold transition flex items-center justify-center gap-2">
        <i class="bi bi-check-lg"></i>
        Simpan Password Baru
      </button>
    </form>

    <!-- Security Tips -->
    <div class="mt-8 pt-8 border-t border-white/10">
      <h3 class="font-semibold mb-3 flex items-center gap-2">
        <i class="bi bi-shield-check text-blue-400"></i>
        Tips Keamanan Password
      </h3>
      <ul class="space-y-2 text-sm text-gray-400">
        <li class="flex items-start gap-2">
          <i class="bi bi-check-circle text-green-400 mt-0.5"></i>
          <span>Gunakan kombinasi huruf besar, kecil, angka, dan simbol</span>
        </li>
        <li class="flex items-start gap-2">
          <i class="bi bi-check-circle text-green-400 mt-0.5"></i>
          <span>Hindari menggunakan informasi pribadi seperti nama atau tanggal lahir</span>
        </li>
        <li class="flex items-start gap-2">
          <i class="bi bi-check-circle text-green-400 mt-0.5"></i>
          <span>Jangan gunakan password yang sama dengan akun lain</span>
        </li>
        <li class="flex items-start gap-2">
          <i class="bi bi-check-circle text-green-400 mt-0.5"></i>
          <span>Ubah password secara berkala untuk keamanan maksimal</span>
        </li>
      </ul>
    </div>
  </div>

</main>

<script>
// Toggle password visibility
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  
  if(input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}

// Password strength checker
const newPasswordInput = document.getElementById('new_password');
const strengthIndicator = document.getElementById('password-strength');
const strengthText = document.getElementById('strength-text');
const strengthBars = [
  document.getElementById('strength-bar-1'),
  document.getElementById('strength-bar-2'),
  document.getElementById('strength-bar-3'),
  document.getElementById('strength-bar-4')
];

newPasswordInput.addEventListener('input', function() {
  const password = this.value;
  
  if(password.length === 0) {
    strengthIndicator.classList.add('hidden');
    return;
  }
  
  strengthIndicator.classList.remove('hidden');
  
  let strength = 0;
  
  // Length check
  if(password.length >= 6) strength++;
  if(password.length >= 10) strength++;
  
  // Complexity checks
  if(/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if(/[0-9]/.test(password)) strength++;
  if(/[^a-zA-Z0-9]/.test(password)) strength++;
  
  // Reset bars
  strengthBars.forEach(bar => {
    bar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500');
    bar.classList.add('bg-gray-700');
  });
  
  // Update bars based on strength
  if(strength <= 2) {
    strengthBars[0].classList.remove('bg-gray-700');
    strengthBars[0].classList.add('bg-red-500');
    strengthText.textContent = 'Lemah';
    strengthText.className = 'text-sm text-red-400';
  } else if(strength === 3) {
    strengthBars[0].classList.remove('bg-gray-700');
    strengthBars[0].classList.add('bg-yellow-500');
    strengthBars[1].classList.remove('bg-gray-700');
    strengthBars[1].classList.add('bg-yellow-500');
    strengthText.textContent = 'Sedang';
    strengthText.className = 'text-sm text-yellow-400';
  } else if(strength === 4) {
    strengthBars.forEach((bar, index) => {
      if(index < 3) {
        bar.classList.remove('bg-gray-700');
        bar.classList.add('bg-blue-500');
      }
    });
    strengthText.textContent = 'Kuat';
    strengthText.className = 'text-sm text-blue-400';
  } else {
    strengthBars.forEach(bar => {
      bar.classList.remove('bg-gray-700');
      bar.classList.add('bg-green-500');
    });
    strengthText.textContent = 'Sangat Kuat';
    strengthText.className = 'text-sm text-green-400';
  }
});

// Password match validation
const confirmPasswordInput = document.getElementById('confirm_password');
confirmPasswordInput.addEventListener('input', function() {
  if(this.value && this.value !== newPasswordInput.value) {
    this.classList.add('border-red-500');
  } else {
    this.classList.remove('border-red-500');
  }
});
</script>
</body>
</html>