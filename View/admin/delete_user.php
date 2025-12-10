<?php
// ============================================
// DELETE USER (delete_user.php)
// Skrip untuk menghapus user berdasarkan ID (metode GET).
// ============================================

session_start();
// Pastikan path ke db.php sudah benar, relatif dari lokasi skrip ini (misalnya: admin/delete_user.php)
require '../../service/db.php'; 

// header('Content-Type: text/plain'); // Opsional: untuk memastikan output hanya teks

// ============================================
// 1. OTORISASI & PROTEKSI
// ============================================

// A. Check otorisasi: Pastikan user login dan memiliki role 'admin'
// Catatan: Jika Anda menggunakan role 'developer' juga, tambahkan pengecekan: 
// if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer'))
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['role'] !== 'developer') {
    http_response_code(403);
    exit('forbidden'); // Akses ditolak
}

// B. Pastikan request berisi ID user dan ID tersebut berupa angka
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('invalid'); // ID tidak valid
}

$user_id = intval($_GET['id']);

// C. Proteksi: admin tidak boleh menghapus dirinya sendiri
if ($user_id == $_SESSION['user_id']) {
    http_response_code(403);
    exit('self-denied'); // Tidak dapat menghapus akun sendiri
}

// ============================================
// 2. EKSEKUSI DELETE
// ============================================

try {
    // Gunakan Prepared Statement untuk mencegah SQL Injection
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "success"; // Berhasil dihapus
    } else {
        // Jika eksekusi kueri gagal (misalnya karena foreign key constraint)
        http_response_code(500);
        // echo "failed: " . $stmt->error; // Opsional: untuk debugging
        echo "failed"; 
    }
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    // echo "error: " . $e->getMessage(); // Opsional: untuk debugging
    echo "error"; 
}

$conn->close();
?>