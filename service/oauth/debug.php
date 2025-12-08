<?php
/**
 * OAuth Configuration Checker & Debugger
 * Gunakan untuk check apakah credentials sudah configured dengan benar
 * 
 * Akses: http://localhost/VSB_project/service/oauth/debug.php
 */

session_start();

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

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Configuration Debug</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1f1f1f 50%, #2d2d2d 100%);
            color: #fff;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #FF2800;
            margin-bottom: 30px;
        }
        .section {
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            backdrop-filter: blur(20px);
        }
        .section h2 {
            color: #fff;
            border-bottom: 2px solid #FF2800;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #ddd;
            min-width: 200px;
        }
        .value {
            font-family: 'Courier New', monospace;
            color: #22c55e;
            word-break: break-all;
            flex: 1;
            text-align: right;
        }
        .value.error {
            color: #ef4444;
        }
        .value.placeholder {
            color: #f97316;
        }
        .status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }
        .status.ok {
            background: #22c55e;
        }
        .status.warning {
            background: #f97316;
        }
        .status.error {
            background: #ef4444;
        }
        .warning-box {
            background: rgba(249, 115, 22, 0.15);
            border-left: 4px solid #f97316;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            color: #fbbf24;
        }
        .success-box {
            background: rgba(34, 197, 94, 0.15);
            border-left: 4px solid #22c55e;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            color: #86efac;
        }
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #FF2800;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 15px;
        }
        .action-btn:hover {
            background: #e61e00;
            transform: translateY(-2px);
        }
        code {
            background: rgba(0,0,0,0.3);
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .checklist {
            list-style: none;
            padding: 0;
        }
        .checklist li {
            padding: 8px 0;
            display: flex;
            align-items: center;
        }
        .checklist li::before {
            content: '□ ';
            color: #f97316;
            font-weight: bold;
            margin-right: 10px;
            font-size: 16px;
        }
        .checklist li.done::before {
            content: '✓ ';
            color: #22c55e;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 OAuth Configuration Debug</h1>

    <?php
    $googleConfigured = getenv('GOOGLE_CLIENT_ID') && 
                       getenv('GOOGLE_CLIENT_ID') !== 'YOUR_GOOGLE_CLIENT_ID' &&
                       getenv('GOOGLE_CLIENT_SECRET') && 
                       getenv('GOOGLE_CLIENT_SECRET') !== 'YOUR_GOOGLE_CLIENT_SECRET';
    
    $facebookConfigured = getenv('FACEBOOK_APP_ID') && 
                         getenv('FACEBOOK_APP_ID') !== 'YOUR_FACEBOOK_APP_ID' &&
                         getenv('FACEBOOK_APP_SECRET') && 
                         getenv('FACEBOOK_APP_SECRET') !== 'YOUR_FACEBOOK_APP_SECRET';
    
    $envFileExists = file_exists(__DIR__ . '/../../.env');
    ?>

    <!-- Overall Status -->
    <div class="section">
        <h2>📊 Status Keseluruhan</h2>
        
        <div class="config-item">
            <span class="label">
                <span class="status <?php echo $envFileExists ? 'ok' : 'warning'; ?>"></span>
                .env File
            </span>
            <span class="value <?php echo $envFileExists ? '' : 'warning'; ?>">
                <?php echo $envFileExists ? '✓ Ada' : '✗ Tidak ada'; ?>
            </span>
        </div>

        <div class="config-item">
            <span class="label">
                <span class="status <?php echo $googleConfigured ? 'ok' : 'error'; ?>"></span>
                Google OAuth
            </span>
            <span class="value <?php echo $googleConfigured ? '' : 'error'; ?>">
                <?php echo $googleConfigured ? '✓ Configured' : '✗ Not Configured'; ?>
            </span>
        </div>

        <div class="config-item">
            <span class="label">
                <span class="status <?php echo $facebookConfigured ? 'ok' : 'error'; ?>"></span>
                Facebook OAuth
            </span>
            <span class="value <?php echo $facebookConfigured ? '' : 'error'; ?>">
                <?php echo $facebookConfigured ? '✓ Configured' : '✗ Not Configured'; ?>
            </span>
        </div>
    </div>

    <!-- Google Configuration -->
    <div class="section">
        <h2>🔷 Google OAuth Configuration</h2>
        
        <?php if (!$googleConfigured): ?>
            <div class="warning-box">
                ⚠️ <strong>Google OAuth belum dikonfigurasi!</strong><br>
                Client ID atau Client Secret masih placeholder atau kosong.
            </div>
        <?php else: ?>
            <div class="success-box">
                ✓ Google OAuth sudah dikonfigurasi dengan benar!
            </div>
        <?php endif; ?>

        <div class="config-item">
            <span class="label">Client ID</span>
            <span class="value <?php echo (getenv('GOOGLE_CLIENT_ID') === 'YOUR_GOOGLE_CLIENT_ID' || !getenv('GOOGLE_CLIENT_ID')) ? 'placeholder' : ''; ?>">
                <?php 
                $id = getenv('GOOGLE_CLIENT_ID');
                if (!$id || $id === 'YOUR_GOOGLE_CLIENT_ID') {
                    echo '❌ Placeholder / Not Set';
                } else {
                    echo substr($id, 0, 20) . '...';
                }
                ?>
            </span>
        </div>

        <div class="config-item">
            <span class="label">Client Secret</span>
            <span class="value <?php echo (getenv('GOOGLE_CLIENT_SECRET') === 'YOUR_GOOGLE_CLIENT_SECRET' || !getenv('GOOGLE_CLIENT_SECRET')) ? 'placeholder' : ''; ?>">
                <?php 
                $secret = getenv('GOOGLE_CLIENT_SECRET');
                if (!$secret || $secret === 'YOUR_GOOGLE_CLIENT_SECRET') {
                    echo '❌ Placeholder / Not Set';
                } else {
                    echo '✓ Configured (hidden)';
                }
                ?>
            </span>
        </div>

        <div class="config-item">
            <span class="label">Redirect URI</span>
            <span class="value"><?php echo htmlspecialchars($oauth['google']['redirect_uri']); ?></span>
        </div>
    </div>

    <!-- Facebook Configuration -->
    <div class="section">
        <h2>🔵 Facebook OAuth Configuration</h2>
        
        <?php if (!$facebookConfigured): ?>
            <div class="warning-box">
                ⚠️ <strong>Facebook OAuth belum dikonfigurasi!</strong><br>
                App ID atau App Secret masih placeholder atau kosong.
            </div>
        <?php else: ?>
            <div class="success-box">
                ✓ Facebook OAuth sudah dikonfigurasi dengan benar!
            </div>
        <?php endif; ?>

        <div class="config-item">
            <span class="label">App ID</span>
            <span class="value <?php echo (getenv('FACEBOOK_APP_ID') === 'YOUR_FACEBOOK_APP_ID' || !getenv('FACEBOOK_APP_ID')) ? 'placeholder' : ''; ?>">
                <?php 
                $id = getenv('FACEBOOK_APP_ID');
                if (!$id || $id === 'YOUR_FACEBOOK_APP_ID') {
                    echo '❌ Placeholder / Not Set';
                } else {
                    echo substr($id, 0, 20) . '...';
                }
                ?>
            </span>
        </div>

        <div class="config-item">
            <span class="label">App Secret</span>
            <span class="value <?php echo (getenv('FACEBOOK_APP_SECRET') === 'YOUR_FACEBOOK_APP_SECRET' || !getenv('FACEBOOK_APP_SECRET')) ? 'placeholder' : ''; ?>">
                <?php 
                $secret = getenv('FACEBOOK_APP_SECRET');
                if (!$secret || $secret === 'YOUR_FACEBOOK_APP_SECRET') {
                    echo '❌ Placeholder / Not Set';
                } else {
                    echo '✓ Configured (hidden)';
                }
                ?>
            </span>
        </div>

        <div class="config-item">
            <span class="label">Redirect URI</span>
            <span class="value"><?php echo htmlspecialchars($oauth['facebook']['redirect_uri']); ?></span>
        </div>
    </div>

    <!-- Setup Instructions -->
    <div class="section">
        <h2>🚀 Setup Checklist</h2>
        
        <h3>Google OAuth Setup:</h3>
        <ul class="checklist">
            <li <?php echo $envFileExists ? 'class="done"' : ''; ?>>
                Create .env file (copy .env.example)
            </li>
            <li>
                Buka <a href="https://console.cloud.google.com" target="_blank" style="color: #60a5fa;">Google Cloud Console</a>
            </li>
            <li>
                Create Project dan Enable Google+ API
            </li>
            <li>
                Create OAuth 2.0 credentials (Web Application)
            </li>
            <li>
                Add Redirect URI ke Google:
                <br><code><?php echo htmlspecialchars($oauth['google']['redirect_uri']); ?></code>
            </li>
            <li <?php echo $googleConfigured ? 'class="done"' : ''; ?>>
                Copy Client ID & Secret ke .env:
                <br><code>GOOGLE_CLIENT_ID=xxx</code>
                <br><code>GOOGLE_CLIENT_SECRET=xxx</code>
            </li>
        </ul>

        <h3 style="margin-top: 25px;">Facebook OAuth Setup:</h3>
        <ul class="checklist">
            <li>
                Buka <a href="https://developers.facebook.com" target="_blank" style="color: #60a5fa;">Facebook Developers</a>
            </li>
            <li>
                Create App dan Add Facebook Login product
            </li>
            <li>
                Add Redirect URI ke Facebook:
                <br><code><?php echo htmlspecialchars($oauth['facebook']['redirect_uri']); ?></code>
            </li>
            <li <?php echo $facebookConfigured ? 'class="done"' : ''; ?>>
                Copy App ID & Secret ke .env:
                <br><code>FACEBOOK_APP_ID=xxx</code>
                <br><code>FACEBOOK_APP_SECRET=xxx</code>
            </li>
        </ul>
    </div>

    <!-- Troubleshooting -->
    <div class="section">
        <h2>❓ Troubleshooting</h2>
        
        <h3>Error: "OAuth client was not found" (401: invalid_client)</h3>
        <p>Artinya credentials tidak valid. Cek:</p>
        <ul>
            <li>✓ Client ID / App ID sudah di-copy dengan benar (tanpa spasi)</li>
            <li>✓ Client Secret / App Secret sudah di-copy dengan benar</li>
            <li>✓ .env file exists dan ter-load dengan benar</li>
            <li>✓ Redirect URI di Google/Facebook match dengan config (URL harus persis sama)</li>
            <li>✓ Refresh page untuk load .env terbaru</li>
        </ul>

        <h3>.env File Tidak Ter-load</h3>
        <p>Jika .env exists tapi tidak ter-load:</p>
        <ul>
            <li>Pastikan .env ada di <strong>project root</strong> (<?php echo __DIR__ . '/../../'; ?>)</li>
            <li>Tidak di /service/oauth/ atau folder lain</li>
            <li>Cek permissions - file harus readable</li>
            <li>Clear browser cache & cookies</li>
            <li>Restart PHP/server</li>
        </ul>

        <h3>Redirect URI Mismatch</h3>
        <p>Jika error "redirect_uri_mismatch":</p>
        <ul>
            <li>Lihat Redirect URI di atas ⬆️</li>
            <li>Copy URL itu persis ke Google/Facebook settings</li>
            <li>Harus sama: protocol, domain, port, path semuanya</li>
            <li>Contoh: <code>http://localhost/VSB_project/service/oauth/google_callback.php</code></li>
        </ul>
    </div>

    <!-- Quick Actions -->
    <div class="section" style="text-align: center;">
        <h2>⚡ Quick Actions</h2>
        
        <a href="<?php echo htmlspecialchars($oauth['google']['auth_url']); ?>?client_id=test" class="action-btn" style="background: #4285f4;">
            🔷 Test Google Connection
        </a>

        <a href="<?php echo htmlspecialchars($oauth['facebook']['auth_url']); ?>?client_id=test" class="action-btn" style="background: #1877f2; margin-left: 10px;">
            🔵 Test Facebook Connection
        </a>

        <a href="javascript:location.reload()" class="action-btn">
            🔄 Refresh Debug Info
        </a>
    </div>

    <!-- Footer -->
    <div style="text-align: center; color: #999; margin-top: 40px; font-size: 12px;">
        <p>Debug page ini hanya untuk development. Hapus di production!</p>
        <p>File: /service/oauth/debug.php</p>
    </div>
</div>

</body>
</html>
