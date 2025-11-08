<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $role = $input['role'] ?? 'staff';
    
    if (empty($username) || empty($password) || empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        exit;
    }
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Username already exists']);
            exit;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            exit;
        }
        
        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $name, $role]);
        
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to create user: ' . $e->getMessage()
        ]);
    }
}
?>