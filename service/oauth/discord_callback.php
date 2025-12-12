<?php
session_start();
require '../../vendor/autoload.php';
include '../db.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// ===== VALIDASI STATE (CSRF PROTECTION) =====
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('Error: Invalid state parameter');
}

// ===== CEK TOKEN DARI DISCORD =====
if (isset($_GET['code'])) {
    $tokenUrl = 'https://discord.com/api/oauth2/token';

    $data = [
        'code' => $_GET['code'],
        'client_id' => $_ENV['DISCORD_CLIENT_ID'],
        'client_secret' => $_ENV['DISCORD_CLIENT_SECRET'],
        'redirect_uri' => $_ENV['DISCORD_REDIRECT_URI'],
        'grant_type' => 'authorization_code'
    ];

    $options = [
        'http' => [
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($tokenUrl, false, $context);
    
    if ($response === false) {
        echo "<h3 style='color:red'>TOKEN ERROR - Request Failed:</h3>";
        echo "<p>URL: " . htmlspecialchars($tokenUrl) . "</p>";
        echo "<p>Data: " . htmlspecialchars(http_build_query($data)) . "</p>";
        echo "<p>Check if redirect_uri matches Discord Developer Portal exactly</p>";
        exit;
    }
    
    $tokenData = json_decode($response, true);

    if (!isset($tokenData['access_token'])) {
        echo "<h3 style='color:red'>TOKEN ERROR:</h3>";
        var_dump($tokenData);
        exit;
    }

    $userUrl = 'https://discord.com/api/users/@me';

    $options = [
        'http' => [
            'header' => 'Authorization: Bearer ' . $tokenData['access_token'],
            'method' => 'GET'
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($userUrl, false, $context);
    $userData = json_decode($response, true);

    if (!isset($userData['id'])) {
        echo "<h3 style='color:red'>USER DATA ERROR:</h3>";
        var_dump($userData);
        exit;
    }

    $discord_id = $userData['id'];
    $username = $userData['username'];
    $email = $userData['email'] ?? null;
    $avatar_hash = $userData['avatar'];
    $avatar_url = "https://cdn.discordapp.com/avatars/{$discord_id}/{$avatar_hash}.png";

    $find = $conn->prepare("SELECT id, discord_id FROM users WHERE discord_id = ? OR email = ?");
    $find->bind_param("ss", $discord_id, $email);
    $find->execute();
    $user = $find->get_result()->fetch_assoc();

    if ($user) {
        $user_id = $user['id'];
        
        if (empty($user['discord_id'])) {
            $update = $conn->prepare("UPDATE users SET discord_id = ? WHERE id = ?");
            $update->bind_param("si", $discord_id, $user_id);
            $update->execute();
        }
    } else {
        $ins = $conn->prepare("INSERT INTO users (discord_id, username, email, profile_pic, level) VALUES (?, ?, ?, ?, 1)");
        $ins->bind_param("ssss", $discord_id, $username, $email, $avatar_url);
        $ins->execute();
        $user_id = $ins->insert_id;
    }

    // ====== Simpan session biar halaman utama kebaca =====
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email']   = $email;
    $_SESSION['name']    = $username;
    $_SESSION['avatar']  = $avatar_url;

    header("Location: ../../View/halaman_utama.php");
    exit;
}

echo "INVALID ACCESS — tidak ada authorization code.";
exit;
?>