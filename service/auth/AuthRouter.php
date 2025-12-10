<?php
session_start();
require_once 'ProviderGoogle.php';
require_once 'ProviderFacebook.php';

$provider = $_GET['provider'] ?? null;

switch($provider){
    case 'google':
        isset($_GET['code'])
            ? ProviderGoogle::callback()
            : ProviderGoogle::login();
        break;

    case 'facebook':
        isset($_GET['code'])
            ? ProviderFacebook::callback()
            : ProviderFacebook::login();
        break;

    default:
        die("Provider tidak valid.");
}
