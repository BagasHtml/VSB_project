<?php
/**
 * Google OAuth Login Handler
 * Redirects user to Google for authentication
 */

session_start();

$oauth = require 'config.oauth.php';
$google = $oauth['google'];

// Store redirect parameter if provided (login vs register)
if (isset($_GET['redirect'])) {
    $_SESSION['oauth_redirect'] = $_GET['redirect'];
}

// Generate state token for security (CSRF protection)
$state = bin2hex(random_bytes(32));
$_SESSION['oauth_state'] = $state;

// Build authorization URL
$params = [
    'client_id' => $google['client_id'],
    'redirect_uri' => $google['redirect_uri'],
    'response_type' => 'code',
    'scope' => $google['scopes'],
    'state' => $state,
    'access_type' => 'offline'
];

$auth_url = $google['auth_url'] . '?' . http_build_query($params);

// Redirect to Google
header('Location: ' . $auth_url);
exit;
?>
