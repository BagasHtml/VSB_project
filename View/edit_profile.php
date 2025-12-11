<?php
session_start();
include '../service/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login_register/form_login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// ambil data user
$stmt = $conn->prepare("SELECT username, title, profile_pic, level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check apakah user adalah developer/admin
$is_developer = ($user['level'] >= 50);

$message = "";

// proses update profil
if (isset($_POST['update'])) {
    $new_username = $_POST['username'];
    $new_title = $_POST['title'] ?? $user['title']; // Only allow title change if developer
    
    // If user is not developer, maintain old title
    if (!$is_developer) {
        $new_title = $user['title'];
    }

    $profile_pic = $user['profile_pic']; // default lama

    // upload file jika ada
    if (!empty($_FILES['profile']['name'])) {
        $file = $_FILES['profile'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "pf_" . time() . "_" . rand(1000,9999) . "." . $ext;

        // folder upload
        $upload_path = "../uploads/profile/" . $filename;
        move_uploaded_file($file['tmp_name'], $upload_path);

        $profile_pic = $filename;
    }

    // update database
    $update = $conn->prepare("UPDATE users SET username=?, title=?, profile_pic=? WHERE id=?");
    $update->bind_param("sssi", $new_username, $new_title, $profile_pic, $user_id);

    if ($update->execute()) {
        $message = "Profil berhasil diperbarui!";
        // update variabel
        $user['username'] = $new_username;
        $user['title'] = $new_title;
        $user['profile_pic'] = $profile_pic;
    } else {
        $message = "Gagal update profil: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile - Knowledge Battle</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
body { font-family: 'Poppins', sans-serif; }
.glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
.gradient-text { background: linear-gradient(to right, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
</style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-white min-h-screen">

<header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-white/10">
  <nav class="max-w-7xl mx-auto flex justify-between items-center py-3 px-5">
    <a href="settings.php" class="flex items-center gap-2 glass px-4 py-2 rounded-full hover:bg-red-600/20 transition">
      <i class="bi bi-arrow-left"></i>
      <span>Kembali</span>
    </a>
    <div class="text-xl font-bold"><span class="text-gray-200">Edit</span><span class="gradient-text">Profil</span></div>
    <div class="w-24"></div>
  </nav>
</header>

<main class="pt-28 max-w-xl mx-auto px-4 pb-16">

  <?php if($message): ?>
    <div class="mb-6 p-4 text-center rounded-xl font-semibold 
    <?= (str_contains($message,'berhasil')?'bg-green-600/30 text-green-300':'bg-red-600/30 text-red-300') ?>">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <div class="glass p-8 rounded-3xl">

    <form method="POST" enctype="multipart/form-data" class="space-y-6">

      <!-- Profile Picture -->
      <div class="flex flex-col items-center">
        <div class="w-28 h-28 rounded-full overflow-hidden bg-gray-700 shadow-lg mb-3">
          <?php if($user['profile_pic']): ?>
            <img src="../uploads/profile/<?= $user['profile_pic'] ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <div class="flex items-center justify-center h-full text-3xl">
              <?= strtoupper(substr($user['username'],0,1)) ?>
            </div>
          <?php endif; ?>
        </div>

        <label class="cursor-pointer bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
          Ganti Foto
          <input type="file" name="profile" class="hidden" accept="image/*">
        </label>
      </div>

      <!-- Username -->
      <div>
        <label class="text-gray-300 font-semibold mb-1 block">Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
        class="w-full p-3 rounded-lg bg-gray-800 border border-white/10 focus:ring-2 ring-red-500 outline-none">
      </div>

      <!-- Title -->
      <div>
        <label class="text-gray-300 font-semibold mb-1 block">
          Title / Status
          <?php if(!$is_developer): ?>
            <span class="text-xs text-gray-500">(Admin/Developer only)</span>
          <?php endif; ?>
        </label>
        <input type="text" name="title" value="<?= htmlspecialchars($user['title']) ?>"
        class="w-full p-3 rounded-lg bg-gray-800 border border-white/10 focus:ring-2 ring-red-500 outline-none <?= !$is_developer ? 'opacity-50 cursor-not-allowed' : '' ?>"
        <?= !$is_developer ? 'disabled' : '' ?>>
        <?php if(!$is_developer): ?>
          <p class="text-xs text-gray-500 mt-1">Hanya admin/developer yang memberi title</p>
        <?php endif; ?>
      </div>

      <button name="update" class="w-full mt-4 bg-red-600 hover:bg-red-700 py-3 rounded-xl font-semibold transition">
        Simpan Perubahan
      </button>
    </form>

  </div>
</main>

</body>
</html>
