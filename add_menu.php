<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $item = $input['item'] ?? '';
    $price = $input['price'] ?? 0;
    $category = $input['category'] ?? 'Main Course';
    $description = $input['description'] ?? '';
    $stock = $input['stock'] ?? 0;
    
    if (empty($item) || $price <= 0) {
        echo json_encode(['success' => false, 'error' => 'Item name and price are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO menu_items (item_name, description, price, category, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$item, $description, $price, $category, $stock]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Menu item added successfully',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to add menu item: ' . $e->getMessage()
        ]);
    }
}
?>