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
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        echo "<h3 style='color:red'>TOKEN ERROR:</h3>";
        var_dump($token);
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
        $ins = $conn->prepare("INSERT INTO users (google_id, username, email, profile_pic, level) VALUES (?, ?, ?, ?, 1)");
        $ins->bind_param("ssss", $g->id, $g->name, $g->email, $g->picture);
        $ins->execute();
        $user_id = $ins->insert_id;
    } else {
        $user_id = $user['id'];
    }

    // ====== Simpan session biar halaman utama kebaca =====
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email']   = $g->email;
    $_SESSION['name']    = $g->name;
    $_SESSION['avatar']  = $g->picture;

    header("Location: ../../View/halaman_utama.php");
    exit;
}

echo "INVALID ACCESS — tidak ada authorization code.";
exit;
