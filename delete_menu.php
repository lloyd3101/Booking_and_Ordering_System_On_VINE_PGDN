<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid menu item ID']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Menu item deleted successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to delete menu item: ' . $e->getMessage()
        ]);
    }
}
?>