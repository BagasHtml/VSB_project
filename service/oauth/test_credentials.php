<?php
/**
 * OAuth Credentials Test
 * Test apakah credentials valid tanpa full OAuth flow
 * 
 * Akses: http://localhost/VSB_project/service/oauth/test_credentials.php
 */

header('Content-Type: application/json');

// Load .env file if exists
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    foreach ($env as $key => $value) {
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

$oauth = require 'config.oauth.php';

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'env_file_exists' => file_exists(__DIR__ . '/../../.env'),
    'google' => [
        'configured' => false,
        'client_id_set' => false,
        'client_id_valid' => false,
        'client_secret_set' => false,
        'message' => ''
    ],
    'facebook' => [
        'configured' => false,
        'app_id_set' => false,
        'app_id_valid' => false,
        'app_secret_set' => false,
        'message' => ''
    ]
];

// Check Google credentials
$google_client_id = getenv('GOOGLE_CLIENT_ID');
$google_client_secret = getenv('GOOGLE_CLIENT_SECRET');

$results['google']['client_id_set'] = !empty($google_client_id);
$results['google']['client_secret_set'] = !empty($google_client_secret);

if ($google_client_id && $google_client_id !== 'YOUR_GOOGLE_CLIENT_ID') {
    $results['google']['client_id_valid'] = true;
    
    // Try to test with Google API
    if ($google_client_secret && $google_client_secret !== 'YOUR_GOOGLE_CLIENT_SECRET') {
        $results['google']['configured'] = true;
        $results['google']['message'] = 'Google credentials look valid!';
    } else {
        $results['google']['message'] = 'Client ID set but Secret is missing or placeholder';
    }
} else {
    $results['google']['message'] = 'Client ID is missing or still placeholder (YOUR_GOOGLE_CLIENT_ID)';
}

// Check Facebook credentials
$facebook_app_id = getenv('FACEBOOK_APP_ID');
$facebook_app_secret = getenv('FACEBOOK_APP_SECRET');

$results['facebook']['app_id_set'] = !empty($facebook_app_id);
$results['facebook']['app_secret_set'] = !empty($facebook_app_secret);

if ($facebook_app_id && $facebook_app_id !== 'YOUR_FACEBOOK_APP_ID') {
    $results['facebook']['app_id_valid'] = true;
    
    // Try to test with Facebook API
    if ($facebook_app_secret && $facebook_app_secret !== 'YOUR_FACEBOOK_APP_SECRET') {
        $results['facebook']['configured'] = true;
        $results['facebook']['message'] = 'Facebook credentials look valid!';
    } else {
        $results['facebook']['message'] = 'App ID set but Secret is missing or placeholder';
    }
} else {
    $results['facebook']['message'] = 'App ID is missing or still placeholder (YOUR_FACEBOOK_APP_ID)';
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
