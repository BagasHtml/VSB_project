<?php
/**
 * Database Migration - Add OAuth Support
 * 
 * Run this file once to add OAuth columns to users table:
 * 1. Open browser: http://localhost/VSB_project/service/oauth/migrate_oauth.php
 * 2. Or run via PHP CLI: php migrate_oauth.php
 * 
 * This adds:
 * - oauth_id (social provider + user ID)
 * - oauth_provider (google, facebook, etc)
 * - is_verified (email verified via OAuth)
 * - profile_picture (store profile image filename)
 */

include '../db.php';

try {
    // Check if oauth_id column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'oauth_id'");
    
    if ($result->num_rows === 0) {
        // Add OAuth columns
        $migrations = [
            "ALTER TABLE users ADD COLUMN oauth_id VARCHAR(255) UNIQUE DEFAULT NULL COMMENT 'OAuth provider ID (e.g., google_123456)'",
            "ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(50) DEFAULT NULL COMMENT 'OAuth provider (google, facebook, etc)'",
            "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 COMMENT 'Email verified flag'",
            "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL COMMENT 'Profile picture filename'"
        ];
        
        foreach ($migrations as $migration) {
            if ($conn->query($migration)) {
                echo "✅ Migration successful: " . substr($migration, 0, 50) . "...<br>";
            } else {
                echo "❌ Migration failed: " . $conn->error . "<br>";
            }
        }
        
        echo "<br><strong>✅ OAuth Support Added Successfully!</strong><br>";
        echo "Database schema updated with OAuth columns.<br><br>";
        
    } else {
        echo "⚠️ OAuth columns already exist. Skipping migration.<br>";
    }
    
    // Show current table structure
    echo "<hr>";
    echo "<h3>Current users table structure:</h3>";
    $result = $conn->query("DESCRIBE users");
    echo "<table border='1' cellpadding='10'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . ($cell ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>
