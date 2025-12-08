<?php
/**
 * Facebook OAuth Login Handler
 * Redirects user to Facebook for authentication
 */

session_start();

$oauth = require 'config.oauth.php';
$facebook = $oauth['facebook'];

// Store redirect parameter if provided (login vs register)
if (isset($_GET['redirect'])) {
    $_SESSION['oauth_redirect'] = $_GET['redirect'];
}

// Generate state token for security (CSRF protection)
$state = bin2hex(random_bytes(32));
$_SESSION['oauth_state'] = $state;

// Build authorization URL
$params = [
    'client_id' => $facebook['app_id'],
    'redirect_uri' => $facebook['redirect_uri'],
    'response_type' => 'code',
    'state' => $state,
    'scope' => 'email,public_profile'
];

$auth_url = $facebook['auth_url'] . '?' . http_build_query($params);

// Redirect to Facebook
header('Location: ' . $auth_url);
exit;
?>
