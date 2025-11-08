<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $customerName = $input['customer_name'] ?? '';
    $items = $input['items'] ?? [];
    $total = $input['total'] ?? 0;
    
    if (empty($customerName) || empty($items) || $total <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid order data']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, total_amount) VALUES (?, ?)");
        $stmt->execute([$customerName, $total]);
        $orderId = $pdo->lastInsertId();
        
        // Add order items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, item_total) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $orderId
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => 'Failed to create order: ' . $e->getMessage()
        ]);
    }
}
?>