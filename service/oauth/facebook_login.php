<?php
session_start();
require_once '../config/oauth.php';
 $facebookConfig = $oauth['facebook'];

// Generate a random state parameter for security
 $_SESSION['oauth_state'] = bin2hex(random_bytes(16));

// Build the authorization URL
 $params = [
    'client_id' => $facebookConfig['app_id'],
    'redirect_uri' => $facebookConfig['redirect_uri'],
    'scope' => 'email',
    'state' => $_SESSION['oauth_state'],
    'response_type' => 'code'
];

 $authUrl = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);

// Redirect to Facebook's authorization page
header('Location: ' . $authUrl);
exit();
?>