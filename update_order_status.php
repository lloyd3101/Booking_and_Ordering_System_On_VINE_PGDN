<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $orderId = $input['order_id'] ?? 0;
    $status = $input['status'] ?? '';
    
    if ($orderId <= 0 || empty($status)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update order status: ' . $e->getMessage()
        ]);
    }
}
?>