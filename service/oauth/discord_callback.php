<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

// Load konfigurasi OAuth
 $oauthConfig = require __DIR__ . '/../config/oauth.php';
 $discordConfig = $oauthConfig['discord'];
 require_once __DIR__ . '/../functions/oauth.php';
processOAuthUser($userData, 'discord', $userData['id']);

// Cek apakah ada kode otorisasi
if (!isset($_GET['code'])) {
    die('Error: Authorization code not found');
}

// Tukar kode otorisasi dengan token akses
 $tokenUrl = 'https://discord.com/api/oauth2/token';
 $data = [
    'code' => $_GET['code'],
    'client_id' => $discordConfig['client_id'],
    'client_secret' => $discordConfig['client_secret'],
    'redirect_uri' => $discordConfig['redirect_uri'],
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
 $response = file_get_contents($tokenUrl, false, $context);
 $tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    die('Error: Failed to get access token');
}

// Dapatkan informasi pengguna
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

// Discord tidak menyediakan email secara default, kita perlu request dengan scope tambahan
// Untuk mendapatkan email, tambahkan scope 'email' saat otorisasi

// Proses data pengguna
processOAuthUser($userData, 'discord', $userData['id']);

// Redirect ke halaman utama
header('Location: ../../halaman_utama.php');
exit;
?>