<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../db.php';
session_start();

$fb = new Facebook\Facebook([
    'app_id' => 'YOUR_APP_ID',   
    'app_secret' => 'YOUR_APP_SECRET',
    'default_graph_version' => 'v19.0',
]);

$helper = $fb->getRedirectLoginHelper();

try {
    $accessToken = $helper->getAccessToken();
} catch(Exception $e){
    header("Location: ../../View/login/form_login.php?error=oauth_failed");
    exit;
}

if(!isset($accessToken)){
    header("Location: ../../View/login/form_login.php?error=oauth_failed");
    exit;
}

try {
    $response = $fb->get('/me?fields=id,name,email,picture.type(large)', $accessToken);
    $fbUser = $response->getGraphUser();
} catch(Exception $e){
    header("Location: ../../View/login/form_login.php?error=oauth_failed");
    exit;
}

$facebook_id = $fbUser['id'];
$name        = $fbUser['name'];
$email       = $fbUser['email'] ?? null;
$profile_pic = $fbUser['picture']['url'];

$stmt = $conn->prepare("SELECT id FROM users WHERE facebook_id=? OR email=? LIMIT 1");
$stmt->bind_param("ss", $facebook_id, $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if($user){
    $uid = $user['id'];
} else {
    $stmt = $conn->prepare("INSERT INTO users (username,email,facebook_id,profile_pic,provider) VALUES (?,?,?,?, 'facebook')");
    $username = explode(" ",$name)[0];
    $stmt->bind_param("ssss",$username,$email,$facebook_id,$profile_pic);
    $stmt->execute();
    $uid = $stmt->insert_id;
}

$_SESSION['user_id'] = $uid;
header("Location: ../../View/halaman_utama.php");
exit;
