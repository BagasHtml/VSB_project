<?php
// config/oauth.php

// 1. Perbaikan Path: Gunakan dirname() untuk path yang lebih robust
// Dari folder 'config', kita naik SATU level untuk mencapai root proyek ('VSB_project')
 $projectRoot = dirname(__DIR__);
require_once $projectRoot . '/vendor/autoload.php';

// Muat variabel dari file .env yang berada di root proyek
 $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
 $dotenv->safeLoad();

/**
 * 2. Helper Function untuk Menghindari Pengulangan (DRY Principle)
 * Fungsi ini mengambil variabel lingkungan dengan cara yang aman dan konsisten.
 *
 * @param string $key Nama variabel lingkungan.
 * @return mixed|null Nilai variabel atau null jika tidak ditemukan.
 */
function getEnvVar(string $key)
{
    return $_ENV[$key] ?? getenv($key);
}

// Kembalikan array konfigurasi
return [
    'google' => [
        'client_id'     => getEnvVar('GOOGLE_CLIENT_ID'),
        'client_secret' => getEnvVar('GOOGLE_CLIENT_SECRET'),
        'redirect_uri'  => getEnvVar('GOOGLE_REDIRECT_URI'),
    ],

    'discord' => [
        'client_id'     => getEnvVar('DISCORD_CLIENT_ID'),
        'client_secret' => getEnvVar('DISCORD_CLIENT_SECRET'),
        'redirect_uri'  => getEnvVar('DISCORD_REDIRECT_URI'),
    ]
];