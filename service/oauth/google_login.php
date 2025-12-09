<?php
session_start();
require '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// ===== KONEKSI GOOGLE =====
$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope(['email', 'profile', 'openid']);
$client->setAccessType('offline');
$client->setPrompt('consent');

// ===== BIKIN STATE TOKEN (prevent hijack) =====
if (!isset($_SESSION['oauth_state'])) {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
}

$authUrl = $client->createAuthUrl() . "&state=" . $_SESSION['oauth_state'];

// Redirect user login google
header("Location: $authUrl");
exit;
?>