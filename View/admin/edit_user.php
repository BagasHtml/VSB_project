<?php
session_start();
require_once '../../service/db.php';

// Cek otorisasi admin
if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')
) {
    header("Location: admin_login.php");
    exit();
}

// Cek apakah ID user dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID User tidak valid.";
    header("Location: admin_panel.php");
    exit();
}

 $userId = $_GET['id'];

// Ambil data user dari database
 $stmt = $conn->prepare("SELECT id, username, email, role, title, level FROM users WHERE id = ?");
 $stmt->bind_param("i", $userId);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "User tidak ditemukan.";
    header("Location: admin_panel.php");
    exit();
}

 $user = $result->fetch_assoc();
 $stmt->close();

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $title = $_POST['title'];
    $level = $_POST['level'];
    
    // Validasi input
    if (empty($username) || empty($email) || empty($role) || empty($level)) {
        $error = "Semua field kecuali title harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Update data user di database
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, title = ?, level = ? WHERE id = ?");
        // PERBAIKAN: Menghapus backtick dan menggunakan variabel $email yang benar
        $stmt->bind_param("sssssi", $username, $email, $role, $title, $level, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data user berhasil diperbarui.";
            header("Location: admin_panel.php");
            exit();
        } else {
            $error = "Gagal memperbarui data user: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Knowledge Battle Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right bottom, #1f2937, #111827);
            min-height: 100vh;
        }
        
        .glass {
            background: rgba(255,255,255,0.05); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
    <div class="container mx-auto p-8">
        <div class="glass max-w-2xl mx-auto p-6 rounded-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit User</h2>
                <a href="admin_panel.php" class="text-gray-400 hover:text-white">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-900/30 border border-red-500 text-red-300 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="post" action="edit_user.php?id=<?= $userId ?>" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" 
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-400 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-400 mb-2">Role</label>
                        <select id="role" name="role" 
                                class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500">
                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="developer" <?= $user['role'] == 'developer' ? 'selected' : '' ?>>Developer</option>
                        </select>
                    </div>
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-400 mb-2">Level</label>
                        <input type="number" id="level" name="level" value="<?= htmlspecialchars($user['level']) ?>" 
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500" required>
                    </div>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-400 mb-2">Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($user['title']) ?>" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-red-500">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="admin_panel.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>