<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? 0;
    $item = $input['item'] ?? '';
    $price = $input['price'] ?? 0;
    $category = $input['category'] ?? '';
    $description = $input['description'] ?? '';
    $stock = $input['stock'] ?? 0;
    $active = $input['active'] ?? true;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid menu item ID']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE menu_items SET item_name = ?, description = ?, price = ?, category = ?, stock = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$item, $description, $price, $category, $stock, $active, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Menu item updated successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update menu item: ' . $e->getMessage()
        ]);
    }
}
?>