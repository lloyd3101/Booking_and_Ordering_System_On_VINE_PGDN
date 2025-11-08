<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT o.*, u.name as customer_name 
        FROM orders o 
        LEFT JOIN users u ON o.customer_id = u.id 
        ORDER BY o.order_date DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order items for each order
    foreach ($orders as &$order) {
        $stmt = $pdo->prepare("
            SELECT oi.*, mi.item_name 
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'orders' => $orders
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch orders: ' . $e->getMessage()
    ]);
}
?>