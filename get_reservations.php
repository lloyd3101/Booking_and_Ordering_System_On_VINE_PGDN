<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT r.*, bp.package_name 
        FROM reservations r 
        LEFT JOIN buffet_packages bp ON r.buffet_package_id = bp.id 
        ORDER BY r.reservation_date DESC, r.created_at DESC
    ");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reservations' => $reservations
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch reservations: ' . $e->getMessage()
    ]);
}
?>