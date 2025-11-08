<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, username, name, email, role, phone, created_at FROM users WHERE role IN ('admin', 'staff') ORDER BY role, name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'users' => $users
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch users: ' . $e->getMessage()
    ]);
}
?>