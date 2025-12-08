<?php
/**
 * Facebook OAuth Callback Handler
 * Processes Facebook's authorization response and logs user in
 */

session_start();
header('Content-Type: application/json');
include '../db.php';

$oauth = require 'config.oauth.php';
$facebook = $oauth['facebook'];

// Validate state token (CSRF protection)
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid state token']));
}

// Check for errors from Facebook
if (isset($_GET['error'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Facebook login failed: ' . $_GET['error']]));
}

// Check for authorization code
if (!isset($_GET['code'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'No authorization code received']));
}

$code = $_GET['code'];

// Exchange code for access token
$token_params = [
    'client_id' => $facebook['app_id'],
    'client_secret' => $facebook['app_secret'],
    'code' => $code,
    'redirect_uri' => $facebook['redirect_uri']
];

$ch = curl_init($facebook['token_url']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Failed to get access token']));
}

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'No access token in response']));
}

// Get user info from Facebook
$userinfo_url = $facebook['userinfo_url'] . '?fields=' . urlencode($facebook['fields']) . '&access_token=' . $tokenData['access_token'];

$ch = curl_init($userinfo_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$userinfo = curl_exec($ch);
curl_close($ch);

$userData = json_decode($userinfo, true);

if (!isset($userData['email']) || !isset($userData['id'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Could not get email from Facebook']));
}

$email = $userData['email'];
$name = $userData['name'] ?? 'User';
$picture = $userData['picture']['data']['url'] ?? null;
$oauth_id = 'facebook_' . $userData['id'];

// Check if user exists
$check_stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? OR oauth_id = ?");
$check_stmt->bind_param("ss", $email, $oauth_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // User exists - login
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    
    // Update OAuth ID if not set
    $update_stmt = $conn->prepare("UPDATE users SET oauth_id = ?, oauth_provider = 'facebook' WHERE id = ?");
    $update_stmt->bind_param("si", $oauth_id, $user_id);
    $update_stmt->execute();
    
} else {
    // New user - create account
    // Generate username from email
    $username = explode('@', $email)[0];
    
    // Check if username exists and generate unique one
    $check_user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $counter = 1;
    $original_username = $username;
    
    while (true) {
        $check_user_stmt->bind_param("s", $username);
        $check_user_stmt->execute();
        
        if ($check_user_stmt->get_result()->num_rows === 0) {
            break;
        }
        
        $username = $original_username . $counter;
        $counter++;
    }
    
    // Generate random password (won't be used since auth via OAuth)
    $password = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);
    $created_at = date('Y-m-d H:i:s');
    $is_verified = 1; // Email verified via Facebook
    
    $insert_stmt = $conn->prepare("INSERT INTO users (email, username, password, oauth_id, oauth_provider, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $provider = 'facebook';
    $insert_stmt->bind_param("sssssss", $email, $username, $password, $oauth_id, $provider, $is_verified, $created_at);
    
    if (!$insert_stmt->execute()) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Failed to create account']));
    }
    
    $user_id = $conn->insert_id;
    
    // Download and save profile picture if available
    if ($picture) {
        $upload_dir = __DIR__ . '/../../uploads/profile/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = 'profile_' . $user_id . '.jpg';
        $filepath = $upload_dir . $filename;
        
        $image = file_get_contents($picture);
        if ($image && file_put_contents($filepath, $image)) {
            // Update user with picture filename
            $pic_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $pic_stmt->bind_param("si", $filename, $user_id);
            $pic_stmt->execute();
        }
    }
}

// Set session
$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;

// Determine redirect
$redirect = $_SESSION['oauth_redirect'] ?? 'login';
unset($_SESSION['oauth_redirect']);
unset($_SESSION['oauth_state']);

// Redirect based on flow
if ($redirect === 'register') {
    header('Location: ../../View/halaman_utama.php?success=oauth_registration');
} else {
    header('Location: ../../View/halaman_utama.php?success=oauth_login');
}

?>
