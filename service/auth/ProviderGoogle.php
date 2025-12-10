<?php
use Google\Client as Google_Client;

class ProviderGoogle 
{
    public static function login() 
    {
        $config = require __DIR__ . '/../config/oauth.php';
        $google = $config['google'];

        $client = new Google_Client();
        $client->setClientId($google['client_id']);
        $client->setClientSecret($google['client_secret']);
        $client->setRedirectUri($google['redirect_uri']);
        $client->addScope(['openid','email','profile']);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        header("Location: " . $client->createAuthUrl());
        exit;
    }

    public static function callback() 
    {
        global $client;
        require_once __DIR__ . '/AuthController.php';

        $config = require __DIR__ . '/../config/oauth.php';
        $google = $config['google'];

        $client = new Google_Client();
        $client->setClientId($google['client_id']);
        $client->setClientSecret($google['client_secret']);
        $client->setRedirectUri($google['redirect_uri']);

        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $info = $client->verifyIdToken();

        AuthController::loginOrRegister(
            'google',
            $info['sub'],             // google_id
            $info['name'],
            $info['email'],
            $info['picture']
        );

        header("Location: ../../View/halaman_utama.php");
        exit;
    }
}
