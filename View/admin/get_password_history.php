<?php
session_start();
include '../../service/db.php';

// Set JSON header
header('Content-Type: application/json');

// Check authorization
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'history' => []
    ]);
    exit();
}

try {
    // Check if password_history table exists
    $tableCheck = $conn->query("
        SELECT COUNT(*) as count 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'password_history'
    ");
    
    $tableExists = $tableCheck->fetch_assoc()['count'] > 0;
    
    if (!$tableExists) {
        echo json_encode([
            'success' => true,
            'message' => 'Table not yet created',
            'history' => []
        ]);
        exit();
    }
    
    // Get password history with user info
    $query = "
        SELECT 
            u.username,
            ph.changed_at,
            ph.changed_by,
            ph.ip_address
        FROM password_history ph
        JOIN users u ON ph.user_id = u.id
        ORDER BY ph.changed_at DESC
        LIMIT 50
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            'username' => htmlspecialchars($row['username']),
            'changed_at' => $row['changed_at'],
            'changed_by' => $row['changed_by'] ? htmlspecialchars($row['changed_by']) : 'System',
            'ip_address' => htmlspecialchars($row['ip_address'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
    exit();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'history' => []
    ]);
    exit();
}

?>