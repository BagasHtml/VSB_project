<?php
function processOAuthUser($userData, $provider, $providerId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE {$provider}_id = ?");
    $stmt->bind_param("s", $providerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
    } else {
        if (!empty($userData['email'])) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $userData['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $updateStmt = $conn->prepare("UPDATE users SET {$provider}_id = ?, avatar_url = ?, login_provider = ? WHERE id = ?");
                $updateStmt->bind_param("sssi", $providerId, $userData['avatar_url'], $provider, $user['id']);
                $updateStmt->execute();
                $_SESSION['user_id'] = $user['id'];
                return;
            }
        }
        
        // Buat pengguna baru
        $insertStmt = $conn->prepare("INSERT INTO users (username, email, {$provider}_id, avatar_url, login_provider, title, level) VALUES (?, ?, ?, ?, ?, 'Member', 1)");
        
        // Generate username jika tidak ada
        $username = !empty($userData['username']) ? $userData['username'] : 
                   (!empty($userData['display_name']) ? $userData['display_name'] : 
                   (!empty($userData['email']) ? explode('@', $userData['email'])[0] : 'user_' . uniqid()));
        
        $email = !empty($userData['email']) ? $userData['email'] : '';
        $avatarUrl = !empty($userData['avatar_url']) ? $userData['avatar_url'] : '';
        
        $insertStmt->bind_param("sssss", $username, $email, $providerId, $avatarUrl, $provider);
        $insertStmt->execute();
        $_SESSION['user_id'] = $conn->insert_id;
    }
}
?>