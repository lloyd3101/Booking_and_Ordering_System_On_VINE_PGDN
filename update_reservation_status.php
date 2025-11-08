<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reservationId = $input['reservation_id'] ?? 0;
    $status = $input['status'] ?? '';
    
    if ($reservationId <= 0 || empty($status)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $reservationId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Reservation status updated successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update reservation status: ' . $e->getMessage()
        ]);
    }
}
?>