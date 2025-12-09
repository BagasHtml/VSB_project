<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

// Load konfigurasi OAuth
 $oauthConfig = require __DIR__ . '/../config/oauth.php';
 $tiktokConfig = $oauthConfig['tiktok'];

// Cek apakah ada kode otorisasi
if (!isset($_GET['code'])) {
    die('Error: Authorization code not found');
}

// Tukar kode otorisasi dengan token akses
 $tokenUrl = 'https://open.tiktokapis.com/v2/oauth/token/';
 $data = [
    'code' => $_GET['code'],
    'client_key' => $tiktokConfig['client_key'],
    'client_secret' => $tiktokConfig['client_secret'],
    'redirect_uri' => $tiktokConfig['redirect_uri'],
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
 $userUrl = 'https://open.tiktokapis.com/v2/user/info/?fields=open_id,union_id,avatar_url,display_name';
 $options = [
    'http' => [
        'header' => 'Authorization: Bearer ' . $tokenData['access_token'],
        'method' => 'GET'
    ]
];

 $context = stream_context_create($options);
 $response = file_get_contents($userUrl, false, $context);
 $userData = json_decode($response, true);

// TikTok API response structure is different
if (!isset($userData['data']['user'])) {
    die('Error: Failed to get user info');
}

 $userInfo = $userData['data']['user'];
 $processedData = [
    'id' => $userInfo['open_id'],
    'username' => $userInfo['display_name'],
    'avatar_url' => $userInfo['avatar_url'],
    'email' => '' // TikTok doesn't provide email
];

// Proses data pengguna
processOAuthUser($processedData, 'tiktok', $processedData['id']);

// Redirect ke halaman utama
header('Location: ../../halaman_utama.php');
exit;
?>