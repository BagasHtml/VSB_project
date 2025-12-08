<?php
// Koneksi database
$conn = mysqli_connect("localhost", "root", "", "VSB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check apakah kolom sudah ada
$result = $conn->query("SHOW COLUMNS FROM posts LIKE 'is_pinned'");
if ($result->num_rows == 0) {
    // Tambah kolom jika belum ada
    if ($conn->query("ALTER TABLE posts ADD COLUMN is_pinned TINYINT(1) DEFAULT 0 AFTER caption") === TRUE) {
        echo "✅ Kolom is_pinned berhasil ditambahkan ke tabel posts!<br><br>";
        echo "Status: Database ready untuk fitur Pinned!";
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    echo "✅ Kolom is_pinned SUDAH ADA di tabel posts<br><br>";
    echo "Status: Database sudah siap!<br>";
    echo "Fitur Pinned siap digunakan.<br><br>";
    
    // Show column info
    $col_info = $conn->query("SHOW COLUMNS FROM posts WHERE Field = 'is_pinned'");
    if ($col_info && $col_info->num_rows > 0) {
        $col = $col_info->fetch_assoc();
        echo "<strong>Column Details:</strong><br>";
        echo "- Field: " . $col['Field'] . "<br>";
        echo "- Type: " . $col['Type'] . "<br>";
        echo "- Default: " . ($col['Default'] !== null ? $col['Default'] : 'NULL') . "<br>";
    }
}

$conn->close();
?>
