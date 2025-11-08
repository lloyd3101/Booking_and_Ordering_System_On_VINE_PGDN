<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $role = $input['role'] ?? '';
    $password = $input['password'] ?? '';
    
    try {
        if ($role === 'admin' || $role === 'staff') {
            $username = $input['username'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ? AND is_active = 1");
            $stmt->execute([$username, $role]);
        } else {
            $email = $input['email'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer' AND is_active = 1");
            $stmt->execute([$email]);
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // For demo purposes - in production, use proper password hashing
        if ($user) {
            // Simple password verification for demo (replace with password_verify in production)
            if ($password === 'Admin123!' && $user['username'] === 'admin') {
                $valid = true;
            } elseif ($password === 'Staff123!' && in_array($user['username'], ['staff1', 'staff2'])) {
                $valid = true;
            } elseif ($password === 'Customer123!' && $user['email'] === 'customer@example.com') {
                $valid = true;
            } else {
                $valid = password_verify($password, $user['password']);
            }
            
            if ($valid) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid password'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>