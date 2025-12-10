<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/AuthController.php';

class ProviderFacebook 
{
    public static function login(){
        $fb = new \Facebook\Facebook([
            'app_id' => 'YOUR_APP_ID',
            'app_secret' => 'YOUR_APP_SECRET',
            'default_graph_version' => 'v19.0',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $callback = "http://localhost/VSB_project/service/auth/AuthRouter.php?provider=facebook";

        header("Location: ".$helper->getLoginUrl($callback,['email']));
        exit;
    }

    public static function callback(){
        $fb = new \Facebook\Facebook([
            'app_id' => 'YOUR_APP_ID',
            'app_secret' => 'YOUR_APP_SECRET',
            'default_graph_version' => 'v19.0',
        ]);
        $helper = $fb->getRedirectLoginHelper();
        $token  = $helper->getAccessToken();

        $info = $fb->get('/me?fields=id,name,email,picture.type(large)',$token)->getGraphUser();

        AuthController::loginOrRegister(
            'facebook',
            $info['id'],
            $info['name'],
            $info['email'] ?? null,
            $info['picture']['url']
        );

        header("Location: ../../View/halaman_utama.php");
        exit;
    }
}
