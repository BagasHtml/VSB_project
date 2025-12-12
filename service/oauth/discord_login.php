<?php
session_start();
require '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Generate state untuk security (CSRF protection)
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Build Discord OAuth URL
$discordAuthUrl = 'https://discord.com/oauth2/authorize?' . http_build_query([
    'client_id' => $_ENV['DISCORD_CLIENT_ID'],
    'redirect_uri' => $_ENV['DISCORD_REDIRECT_URI'],
    'response_type' => 'code',
    'scope' => 'identify email',
    'state' => $state
]);

// Redirect ke Discord authorization page
header("Location: " . $discordAuthUrl);
exit;
?>