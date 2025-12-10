<?php
require_once __DIR__ . '/../../vendor/autoload.php';
session_start();

$fb = new \Facebook\Facebook([
    'app_id' => 'YOUR_APP_ID',       // ← Ganti
    'app_secret' => 'YOUR_APP_SECRET', // ← Ganti
    'default_graph_version' => 'v19.0',
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; 
$callback = "http://localhost/VSB_project/service/oauth/facebook_callback.php";

$loginUrl = $helper->getLoginUrl($callback, $permissions);

header("Location: $loginUrl");
exit;
