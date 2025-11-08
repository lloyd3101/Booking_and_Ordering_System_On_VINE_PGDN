<?php
// approve_handler.php - Handle order and reservation approvals
require_once 'config.php';

class ApprovalHandler {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Approve an order
    public function approveOrder($orderId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = 'Confirmed', 
                    approved_at = NOW(),
                    approved_by = :staff_id
                WHERE id = :order_id AND status = 'Pending'
            ");
            
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':staff_id', $this->getStaffId()); // You'll need to implement staff session
            
            $result = $stmt->execute();
            
            if ($result && $stmt->rowCount() > 0) {
                // Update sales tracking
                $this->updateSalesForOrder($orderId);
                return ['success' => true, 'message' => 'Order approved successfully'];
            } else {
                return ['success' => false, 'message' => 'Order not found or already processed'];
            }
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Reject an order
    public function rejectOrder($orderId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = 'Rejected', 
                    rejected_at = NOW(),
                    rejected_by = :staff_id
                WHERE id = :order_id AND status = 'Pending'
            ");
            
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':staff_id', $this->getStaffId());
            
            $result = $stmt->execute();
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Order rejected successfully'];
            } else {
                return ['success' => false, 'message' => 'Order not found or already processed'];
            }
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Approve a reservation
    public function approveReservation($reservationId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE reservations 
                SET status = 'Confirmed', 
                    approved_at = NOW(),
                    approved_by = :staff_id
                WHERE id = :reservation_id AND status = 'Pending'
            ");
            
            $stmt->bindParam(':reservation_id', $reservationId);
            $stmt->bindParam(':staff_id', $this->getStaffId());
            
            $result = $stmt->execute();
            
            if ($result && $stmt->rowCount() > 0) {
                // Update sales tracking for buffet if applicable
                $this->updateSalesForReservation($reservationId);
                return ['success' => true, 'message' => 'Reservation approved successfully'];
            } else {
                return ['success' => false, 'message' => 'Reservation not found or already processed'];
            }
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Reject a reservation
    public function rejectReservation($reservationId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE reservations 
                SET status = 'Rejected', 
                    rejected_at = NOW(),
                    rejected_by = :staff_id
                WHERE id = :reservation_id AND status = 'Pending'
            ");
            
            $stmt->bindParam(':reservation_id', $reservationId);
            $stmt->bindParam(':staff_id', $this->getStaffId());
            
            $result = $stmt->execute();
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Reservation rejected successfully'];
            } else {
                return ['success' => false, 'message' => 'Reservation not found or already processed'];
            }
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Update sales tracking for approved order
    private function updateSalesForOrder($orderId) {
        try {
            // Get order total
            $stmt = $this->db->prepare("SELECT total_amount FROM orders WHERE id = :order_id");
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
            $order = $stmt->fetch();
            
            if ($order && $order['total_amount'] > 0) {
                $this->recordSale($order['total_amount'], 'order', $orderId);
            }
        } catch(PDOException $e) {
            // Log error but don't fail the approval
            error_log("Sales tracking error for order {$orderId}: " . $e->getMessage());
        }
    }
    
    // Update sales tracking for approved reservation with buffet
    private function updateSalesForReservation($reservationId) {
        try {
            // Get reservation with buffet details
            $stmt = $this->db->prepare("
                SELECT r.id, r.buffet_package_name, b.price as buffet_price
                FROM reservations r
                LEFT JOIN buffet_packages b ON r.buffet_package_id = b.id
                WHERE r.id = :reservation_id
            ");
            $stmt->bindParam(':reservation_id', $reservationId);
            $stmt->execute();
            $reservation = $stmt->fetch();
            
            if ($reservation && $reservation['buffet_package_name'] && $reservation['buffet_price'] > 0) {
                $this->recordSale($reservation['buffet_price'], 'buffet', $reservationId);
            }
        } catch(PDOException $e) {
            // Log error but don't fail the approval
            error_log("Sales tracking error for reservation {$reservationId}: " . $e->getMessage());
        }
    }
    
    // Record sale in sales table
    private function recordSale($amount, $type, $referenceId) {
        $stmt = $this->db->prepare("
            INSERT INTO sales (amount, type, reference_id, sale_date, recorded_by)
            VALUES (:amount, :type, :reference_id, NOW(), :staff_id)
        ");
        
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':reference_id', $referenceId);
        $stmt->bindParam(':staff_id', $this->getStaffId());
        
        $stmt->execute();
    }
    
    // Get staff ID from session (you'll need to implement proper session handling)
    private function getStaffId() {
        // This is a placeholder - implement your session management
        session_start();
        return $_SESSION['staff_id'] ?? 1; // Default to admin if no session
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $handler = new ApprovalHandler();
    $response = [];
    
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $action = $_POST['action'];
        $id = $_POST['id'];
        $type = $_POST['type'] ?? ''; // 'order' or 'reservation'
        
        switch ($action) {
            case 'approve':
                if ($type === 'order') {
                    $response = $handler->approveOrder($id);
                } elseif ($type === 'reservation') {
                    $response = $handler->approveReservation($id);
                }
                break;
                
            case 'reject':
                if ($type === 'order') {
                    $response = $handler->rejectOrder($id);
                } elseif ($type === 'reservation') {
                    $response = $handler->rejectReservation($id);
                }
                break;
                
            default:
                $response = ['success' => false, 'message' => 'Invalid action'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Missing parameters'];
    }
    
    echo json_encode($response);
    exit;
}
?>