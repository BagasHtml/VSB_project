<?php
session_start();
require_once '../../vendor/autoload.php';
use Google\Client as Google_Client;

// Load config
$oauthConfig = require_once '../config/oauth.php';
$googleConfig = $oauthConfig['google'];

// Verifikasi state
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('Invalid OAuth state.');
}

// Buat Google Client
$client = new Google_Client();
$client->setClientId($googleConfig['client_id']);
$client->setClientSecret($googleConfig['client_secret']);
$client->setRedirectUri($googleConfig['redirect_uri']);

// Ambil code dari Google
if (!isset($_GET['code'])) {
    die('No authorization code returned.');
}

$code = $_GET['code'];

// Tukar code dengan access token
$token = $client->fetchAccessTokenWithAuthCode($code);

// Cek error
if (isset($token['error'])) {
    die('Error fetching access token: ' . htmlspecialchars($token['error']));
}

// Set access token di client
$client->setAccessToken($token['access_token']);

// Ambil user info
$oauth2 = new \Google\Service\Oauth2($client);
$userInfo = $oauth2->userinfo->get();

// Tampilkan info user
echo "<h1>Login berhasil!</h1>";
echo "<p><strong>ID:</strong> " . htmlspecialchars($userInfo->id) . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($userInfo->email) . "</p>";
echo "<p><strong>Nama:</strong> " . htmlspecialchars($userInfo->name) . "</p>";
echo "<p><img src='" . htmlspecialchars($userInfo->picture) . "' alt='Avatar'></p>";
?>