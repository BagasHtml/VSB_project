<?php
session_start();

require_once '../../vendor/autoload.php';
use Google\Client as Google_Client;

// Load config
$oauthConfig = require_once '../config/oauth.php';
$googleConfig = $oauthConfig['google'];

// Debugging
echo "<h1>Debugging Google OAuth</h1>";
echo "<p><strong>Client ID:</strong> " . htmlspecialchars($googleConfig['client_id']) . "</p>";
echo "<p><strong>Redirect URI:</strong> " . htmlspecialchars($googleConfig['redirect_uri']) . "</p>";

if (empty($googleConfig['client_id'])) {
    die("<p style='color:red;'><strong>ERROR:</strong> Client ID masih kosong! Periksa file .env.</p>");
}

// Buat Google Client
$client = new Google_Client();
$client->setClientId($googleConfig['client_id']);
$client->setClientSecret($googleConfig['client_secret']);
$client->setRedirectUri($googleConfig['redirect_uri']);
$client->addScope(['openid', 'email', 'profile']);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Generate state untuk security
if (!isset($_SESSION['oauth_state'])) {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
}

// Build Auth URL
$params = [
    'response_type' => 'code',
    'client_id' => $googleConfig['client_id'],
    'redirect_uri' => $googleConfig['redirect_uri'],
    'scope' => 'openid email profile',
    'state' => $_SESSION['oauth_state'],
    'access_type' => 'offline',
    'prompt' => 'consent'
];
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

// Tampilkan URL untuk debugging
echo "<p><strong>Full Auth URL:</strong></p>";
echo "<textarea style='width:100%; height:200px;'>" . htmlspecialchars($authUrl) . "</textarea>";
echo "<p><a href='" . htmlspecialchars($authUrl) . "' style='background:#4285F4;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Klik untuk login ke Google</a></p>";

// Production: redirect langsung
// header('Location: ' . $authUrl);
// exit();
