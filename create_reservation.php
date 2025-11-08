<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'] ?? '';
    $contact = $input['contact'] ?? '';
    $date = $input['date'] ?? '';
    $start = $input['start'] ?? '';
    $end = $input['end'] ?? '';
    $guests = $input['guests'] ?? 0;
    $event = $input['event'] ?? '';
    $buffetPackageId = $input['buffet_package_id'] ?? null;
    $buffetPersons = $input['buffet_persons'] ?? 0;
    $buffetPrice = $input['buffet_price'] ?? 0;
    
    if (empty($name) || empty($contact) || empty($date) || empty($start) || $guests <= 0) {
        echo json_encode(['success' => false, 'error' => 'Please fill all required fields']);
        exit;
    }
    
    try {
        $totalAmount = $buffetPrice;
        
        $stmt = $pdo->prepare("
            INSERT INTO reservations 
            (customer_name, contact_number, reservation_date, start_time, end_time, guests, event_type, buffet_package_id, buffet_persons, buffet_price, total_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name, $contact, $date, $start, $end, $guests, $event, 
            $buffetPackageId, $buffetPersons, $buffetPrice, $totalAmount
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Reservation submitted successfully',
            'reservation_id' => $pdo->lastInsertId()
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to create reservation: ' . $e->getMessage()
        ]);
    }
}
?>