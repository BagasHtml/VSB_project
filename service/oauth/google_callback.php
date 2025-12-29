<?php
session_start();
require '../../vendor/autoload.php';
include '../db.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope(['email','profile','openid']);

// ===== CEK TOKEN DARI GOOGLE =====
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            session_destroy();
            header("Location: ../../View/login.php?error=oauth_failed");
            exit;
        }
        
        $client->setAccessToken($token);
        $oauth = new Google\Service\Oauth2($client);
        $g = $oauth->userinfo->get();
        
        // === CEK user di database ===
        $find = $conn->prepare("SELECT id FROM users WHERE google_id = ?");
        $find->bind_param("s", $g->id);
        $find->execute();
        $user = $find->get_result()->fetch_assoc();
        
        // Jika baru pertama kali login → buat akun otomatis
        if (!$user) {
            // Generate random password untuk OAuth users (tidak akan dipakai)
            $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            
            $ins = $conn->prepare("INSERT INTO users (google_id, username, email, profile_pic, password, level) VALUES (?, ?, ?, ?, ?, 1)");
            $ins->bind_param("sssss", $g->id, $g->name, $g->email, $g->picture, $random_password);
            $ins->execute();
            $user_id = $ins->insert_id;
        } else {
            $user_id = $user['id'];
        }
        
        // ====== Simpan session =====
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email']   = $g->email;
        $_SESSION['name']    = $g->name;
        $_SESSION['avatar']  = $g->picture;
        
        header("Location: ../../View/halaman_utama.php");
        exit;
        
    } catch (Exception $e) {
        session_destroy();
        header("Location: ../../View/login.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: ../../View/login.php?error=no_code");
exit;
?>