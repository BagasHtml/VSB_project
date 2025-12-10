<?php
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// =======================
// MAIL CONFIG
// =======================
defined('MAIL_HOST')        || define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
defined('MAIL_PORT')        || define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
defined('MAIL_USERNAME')    || define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
defined('MAIL_PASSWORD')    || define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
defined('MAIL_FROM_NAME')   || define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Knowledge Battle');
defined('MAIL_FROM_ADDRESS')|| define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? '');

// =======================
// DATABASE CONFIG
// =======================
defined('DB_HOST')  || define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
defined('DB_PORT')  || define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
defined('DB_NAME')  || define('DB_NAME', $_ENV['DB_NAME'] ?? 'vsb');
defined('DB_USER')  || define('DB_USER', $_ENV['DB_USER'] ?? 'root');
defined('DB_PASS')  || define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// =======================
// APP CONFIG
// =======================
defined('APP_ENV')   || define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
defined('APP_DEBUG') || define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);
defined('APP_URL')   || define('APP_URL', rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'));

// =======================
// DEBUG MODE
// =======================
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
