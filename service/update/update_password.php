<?php
include '../../service/db.php';
$email = 'bagas124@gmail.com';
$new_password = 'admin123'; // Password yang mau dipakai
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed, $email);

if($stmt->execute()) {
    echo "✅ Password berhasil diupdate!<br>";
    echo "Login dengan:<br>";
    echo "Email: bagas124@gmail.com<br>";
    echo "Password: admin123";
} else {
    echo "❌ Error: " . $conn->error;
}
?>