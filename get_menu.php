<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_active = 1 ORDER BY category, item_name");
    $menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'menu' => $menu
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
    'success' => true,
    'data' => [
        'menu' => $menu // array of menu items
    ]
]);
}
?>