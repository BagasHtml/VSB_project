<?php
/**
 * PASSWORD HASHING & SECURITY LIBRARY
 * Knowledge Battle Admin Panel
 * 
 * Lokasi: service/password_hash.php
 * Include: include 'service/password_hash.php';
 */

// ============================================
// 1. HASH PASSWORD DENGAN BCRYPT
// ============================================
if (!function_exists('hashPassword')) {
    function hashPassword($password, $cost = 12) {
        if (empty($password)) {
            throw new Exception("Password tidak boleh kosong");
        }
        $options = ['cost' => $cost];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
}

// ============================================
// 2. VERIFIKASI PASSWORD
// ============================================
if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        if (empty($password) || empty($hash)) {
            return false;
        }
        return password_verify($password, $hash);
    }
}

// ============================================
// 3. CEK APAKAH PASSWORD PERLU REHASH
// ============================================
if (!function_exists('needsRehash')) {
    function needsRehash($hash, $cost = 12) {
        $options = ['cost' => $cost];
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);
    }
}

// ============================================
// 4. VALIDASI STRENGTH PASSWORD
// ============================================
if (!function_exists('validatePasswordStrength')) {
    function validatePasswordStrength($password) {
        $errors = [];
        $strength = 0;
        
        // Cek panjang minimum
        if (strlen($password) < 8) {
            $errors[] = "Password minimal 8 karakter";
        } else {
            $strength += 20;
        }
        
        // Cek huruf besar
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password harus mengandung huruf besar (A-Z)";
        } else {
            $strength += 20;
        }
        
        // Cek huruf kecil
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password harus mengandung huruf kecil (a-z)";
        } else {
            $strength += 20;
        }
        
        // Cek angka
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password harus mengandung angka (0-9)";
        } else {
            $strength += 20;
        }
        
        // Cek karakter spesial
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password)) {
            $errors[] = "Password harus mengandung karakter spesial (!@#$%^&* dll)";
        } else {
            $strength += 20;
        }
        
        // Tentukan level kekuatan
        $strengthLevel = 'Lemah';
        if ($strength >= 80) {
            $strengthLevel = 'Sangat Kuat';
        } elseif ($strength >= 60) {
            $strengthLevel = 'Kuat';
        } elseif ($strength >= 40) {
            $strengthLevel = 'Sedang';
        }
        
        return [
            'valid' => count($errors) === 0,
            'strength' => $strengthLevel,
            'strength_score' => $strength,
            'errors' => $errors
        ];
    }
}

// ============================================
// 5. GENERATE RANDOM PASSWORD
// ============================================
if (!function_exists('generateRandomPassword')) {
    function generateRandomPassword($length = 16, $includeSpecial = true) {
        if ($length < 8) {
            $length = 8;
        }
        
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $characters = $uppercase . $lowercase . $numbers;
        if ($includeSpecial) {
            $characters .= $special;
        }
        
        $password = '';
        $charLength = strlen($characters);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charLength - 1)];
        }
        
        return str_shuffle($password);
    }
}

// ============================================
// 6. SANITASI PASSWORD INPUT
// ============================================
if (!function_exists('sanitizePassword')) {
    function sanitizePassword($password) {
        $password = trim($password);
        
        if (strlen($password) > 255) {
            throw new Exception("Password terlalu panjang");
        }
        
        if (strlen($password) < 1) {
            throw new Exception("Password tidak boleh kosong");
        }
        
        return $password;
    }
}

// ============================================
// 7. UPDATE PASSWORD DI DATABASE
// ============================================
if (!function_exists('updatePasswordInDB')) {
    function updatePasswordInDB($conn, $userId, $newPassword) {
        try {
            $newPassword = sanitizePassword($newPassword);
            
            $validation = validatePasswordStrength($newPassword);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => implode(', ', $validation['errors'])
                ];
            }
            
            $hashedPassword = hashPassword($newPassword);
            
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            if (isset($_SESSION['admin_id'])) {
                logPasswordChange($conn, $userId, $_SESSION['admin_id']);
            }
            
            return [
                'success' => true,
                'message' => 'Password berhasil diupdate'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

// ============================================
// 8. VERIFY CURRENT PASSWORD
// ============================================
if (!function_exists('verifyCurrentPassword')) {
    function verifyCurrentPassword($conn, $userId, $currentPassword) {
        try {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if (!$user) {
                return false;
            }
            
            return verifyPassword($currentPassword, $user['password']);
            
        } catch (Exception $e) {
            return false;
        }
    }
}

// ============================================
// 9. VALIDASI PASSWORD RULES
// ============================================
if (!function_exists('isValidPassword')) {
    function isValidPassword($password, $minLength = 8) {
        $password = trim($password);
        
        if (strlen($password) < $minLength) {
            return false;
        }
        
        if (strlen($password) > 255) {
            return false;
        }
        
        if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
}

// ============================================
// 10. LOG PASSWORD CHANGE
// ============================================
if (!function_exists('logPasswordChange')) {
    function logPasswordChange($conn, $userId, $changedBy = null) {
        try {
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS password_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    changed_by VARCHAR(255),
                    ip_address VARCHAR(45),
                    user_agent VARCHAR(500),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ";
            
            $conn->query($createTableSQL);
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);
            
            // Convert changedBy to string if it's an ID
            if (is_numeric($changedBy)) {
                $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $changedBy);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stmt->close();
                    $changedBy = $row ? $row['username'] : 'System';
                } else {
                    $changedBy = 'System';
                }
            }
            
            $stmt = $conn->prepare("
                INSERT INTO password_history (user_id, changed_by, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("isss", $userId, $changedBy, $ipAddress, $userAgent);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            return false;
        }
    }
}

// ============================================
// 11. GET PASSWORD HISTORY
// ============================================
if (!function_exists('getPasswordHistory')) {
    function getPasswordHistory($conn, $limit = 50) {
        try {
            $tableCheck = $conn->query("
                SELECT COUNT(*) as count 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'password_history'
            ");
            
            if ($tableCheck->fetch_assoc()['count'] == 0) {
                return [];
            }
            
            $query = "
                SELECT 
                    u.username,
                    ph.changed_at,
                    ph.changed_by,
                    ph.ip_address
                FROM password_history ph
                JOIN users u ON ph.user_id = u.id
                ORDER BY ph.changed_at DESC
                LIMIT " . intval($limit);
            
            $result = $conn->query($query);
            
            if (!$result) {
                return [];
            }
            
            $history = [];
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
            
            return $history;
            
        } catch (Exception $e) {
            return [];
        }
    }
}

// ============================================
// 12. CHANGE PASSWORD (WRAPPER)
// ============================================
if (!function_exists('changePassword')) {
    function changePassword($conn, $userId, $newPassword, $requireOldPassword = false, $oldPassword = null) {
        try {
            if ($requireOldPassword && $oldPassword) {
                if (!verifyCurrentPassword($conn, $userId, $oldPassword)) {
                    return [
                        'success' => false,
                        'message' => 'Password lama tidak sesuai'
                    ];
                }
            }
            
            $validation = validatePasswordStrength($newPassword);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => implode(', ', $validation['errors'])
                ];
            }
            
            $hashedPassword = hashPassword($newPassword);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Database error'
                ];
            }
            
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengupdate password'
                ];
            }
            
            $stmt->close();
            
            logPasswordChange($conn, $userId, $_SESSION['admin_id'] ?? 'System');
            
            return [
                'success' => true,
                'message' => 'Password berhasil diubah'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

?>