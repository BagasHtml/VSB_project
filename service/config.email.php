<?php
/**
 * EMAIL CONFIGURATION FILE
 * 
 * ⚠️ IMPORTANT: This file contains sensitive credentials
 * - Do NOT commit to version control (.gitignore this file)
 * - Do NOT share with others
 * - Each user/server should have their own credentials
 * 
 * SETUP OPTIONS (Choose One):
 * 
 * OPTION A: Direct Configuration (Below)
 * - Edit the values below
 * - Each user edits their own copy
 * 
 * OPTION B: Environment Variables (Recommended for Production)
 * - Set in .env or server environment
 * - More secure, no credentials in files
 * - Example: $_ENV['SMTP_USERNAME']
 * 
 * OPTION C: .env File (Recommended for Local Development)
 * - Create .env file in project root
 * - Load with dotenv package
 */

// Try to load from environment variables first (most secure)
$username = getenv('SMTP_USERNAME') ?: 'your-email@gmail.com';
$password = getenv('SMTP_PASSWORD') ?: 'your-app-password';
$fromEmail = getenv('SMTP_FROM') ?: 'your-email@gmail.com';
$fromName = getenv('SMTP_FROM_NAME') ?: 'Knowledge Battle';

return [
    'smtp' => [
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',     // SMTP Server
        'port' => getenv('SMTP_PORT') ?: 587,                  // Port
        'secure' => getenv('SMTP_SECURE') ?: 'tls',            // tls or ssl
        'auth' => true,                                         // Enable authentication
        'username' => $username,
        'password' => $password,
    ],
    'from' => [
        'email' => $fromEmail,
        'name' => $fromName,
    ],
    'debug' => getenv('SMTP_DEBUG') ? true : false,            // Debug logging
];

/**
 * CONFIGURATION INSTRUCTIONS FOR USERS:
 * 
 * METHOD 1: Edit This File Directly
 * ────────────────────────────────────────────────────────────
 * 1. Find lines with 'your-email@gmail.com' and 'your-app-password'
 * 2. Replace with YOUR email credentials
 * 3. Save file
 * 4. System will use your credentials
 * 
 * METHOD 2: Use Environment Variables (Recommended)
 * ────────────────────────────────────────────────────────────
 * Set these environment variables in your server:
 * 
 *   SMTP_USERNAME = your-email@gmail.com
 *   SMTP_PASSWORD = your-app-password
 *   SMTP_FROM = your-email@gmail.com
 *   SMTP_FROM_NAME = Your App Name
 *   SMTP_HOST = smtp.gmail.com
 *   SMTP_PORT = 587
 *   SMTP_SECURE = tls
 * 
 * METHOD 3: Create .env File
 * ────────────────────────────────────────────────────────────
 * Create file: .env (in project root)
 * Add:
 *   SMTP_USERNAME=your-email@gmail.com
 *   SMTP_PASSWORD=your-app-password
 *   SMTP_FROM=your-email@gmail.com
 *   SMTP_FROM_NAME=Your App Name
 * 
 * Then add to .gitignore:
 *   .env
 * 
 * Load with (add to beginning of send_otp.php):
 *   if (file_exists(__DIR__ . '/../.env')) {
 *       $env = parse_ini_file(__DIR__ . '/../.env');
 *       foreach ($env as $key => $value) {
 *           putenv("$key=$value");
 *       }
 *   }
 */

?>
