<?php
session_start();
require_once '../config/oauth.php';
require_once '../db.php';

// Verify the state parameter to prevent CSRF attacks
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    header('Location: ../View/login_register/form_login.php?error=invalid_oauth_state');
    exit();
}

// Exchange authorization code for access token
 $tokenUrl = 'https://graph.facebook.com/v18.0/oauth/access_token';
 $params = [
    'code' => $_GET['code'],
    'client_id' => $oauth['facebook']['app_id'],
    'client_secret' => $oauth['facebook']['app_secret'],
    'redirect_uri' => $oauth['facebook']['redirect_uri']
];

 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 $response = curl_exec($ch);
curl_close($ch);

 $tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    header('Location: ../View/login_register/form_login.php?error=token_exchange_failed');
    exit();
}

// Get user information
 $userUrl = 'https://graph.facebook.com/me?fields=id,name,email,picture&access_token=' . $tokenData['access_token'];
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 $response = curl_exec($ch);
curl_close($ch);

 $userData = json_decode($response, true);

if (!isset($userData['id'])) {
    header('Location: ../View/login_register/form_login.php?error=user_info_failed');
    exit();
}

// Process user data
processOAuthUser($userData, 'facebook', $userData['id']);

// Redirect to dashboard
header('Location: ../halaman_utama.php');
exit();

function processOAuthUser($userData, $provider, $providerId) {
    global $conn;
    
    // Check if user exists by provider ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE {$provider}_id = ?");
    $stmt->bind_param("s", $providerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, log them in
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
    } else {
        // Check if user exists by email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $userData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing user with provider ID
            $user = $result->fetch_assoc();
            $updateStmt = $conn->prepare("UPDATE users SET {$provider}_id = ?, avatar_url = ?, login_provider = ? WHERE id = ?");
            $avatarUrl = isset($userData['picture']['data']['url']) ? $userData['picture']['data']['url'] : '';
            $updateStmt->bind_param("sssi", $providerId, $avatarUrl, $provider, $user['id']);
            $updateStmt->execute();
            $_SESSION['user_id'] = $user['id'];
        } else {
            // Create new user
            $insertStmt = $conn->prepare("INSERT INTO users (username, email, {$provider}_id, avatar_url, login_provider, title, level) VALUES (?, ?, ?, ?, ?, 'Member', 1)");
            $username = $userData['name'];
            $avatarUrl = isset($userData['picture']['data']['url']) ? $userData['picture']['data']['url'] : '';
            $insertStmt->bind_param("sssss", $username, $userData['email'], $providerId, $avatarUrl, $provider);
            $insertStmt->execute();
            $_SESSION['user_id'] = $conn->insert_id;
        }
    }
}
?>