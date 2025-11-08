<?php
// buffet_packages.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

$response = ['success' => false, 'message' => 'Unknown error', 'data' => []];

try {
    $pdo = getPDO();
    
    // Get the request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Get all buffet packages
        $stmt = $pdo->query("
            SELECT id, package_name, base_price, inclusions, is_active, created_at 
            FROM buffet_packages 
            WHERE is_active = 1
            ORDER BY base_price DESC
        ");
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the response to match expected structure
        $formattedPackages = [];
        foreach ($packages as $package) {
            // Decode inclusions from JSON string to array
            $inclusions = json_decode($package['inclusions'], true);
            if (!is_array($inclusions)) {
                $inclusions = [];
            }
            
            $formattedPackages[] = [
                'id' => (int)$package['id'],
                'name' => $package['package_name'],
                'price' => floatval($package['base_price']),
                'inclusions' => $inclusions,
                'is_active' => boolval($package['is_active'])
            ];
        }
        
        $response = [
            'success' => true,
            'message' => 'Buffet packages retrieved successfully',
            'data' => ['packages' => $formattedPackages]
        ];
        
    } elseif ($method === 'POST') {
        // Create new buffet package
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name']) || !isset($input['price'])) {
            throw new Exception('Missing required fields: name and price');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO buffet_packages (package_name, base_price, inclusions, is_active, created_at) 
            VALUES (?, ?, ?, 1, NOW())
        ");
        
        $inclusionsJson = json_encode($input['inclusions'] ?: []);
        $success = $stmt->execute([
            $input['name'],
            floatval($input['price']),
            $inclusionsJson
        ]);
        
        if ($success) {
            $response = [
                'success' => true,
                'message' => 'Buffet package created successfully',
                'data' => ['id' => $pdo->lastInsertId()]
            ];
        } else {
            throw new Exception('Failed to create buffet package');
        }
        
    } elseif ($method === 'PUT') {
        // Update buffet package
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? $input['id'] ?? 0;
        
        if (!$id || !$input) {
            throw new Exception('Missing package ID or data');
        }
        
        $stmt = $pdo->prepare("
            UPDATE buffet_packages 
            SET package_name = ?, base_price = ?, inclusions = ? 
            WHERE id = ?
        ");
        
        $inclusionsJson = json_encode($input['inclusions'] ?: []);
        $success = $stmt->execute([
            $input['name'],
            floatval($input['price']),
            $inclusionsJson,
            intval($id)
        ]);
        
        if ($success) {
            $response = [
                'success' => true,
                'message' => 'Buffet package updated successfully'
            ];
        } else {
            throw new Exception('Failed to update buffet package');
        }
        
    } elseif ($method === 'DELETE') {
        // Delete (deactivate) buffet package
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            throw new Exception('Missing package ID');
        }
        
        $stmt = $pdo->prepare("UPDATE buffet_packages SET is_active = 0 WHERE id = ?");
        $success = $stmt->execute([intval($id)]);
        
        if ($success) {
            $response = [
                'success' => true,
                'message' => 'Buffet package deleted successfully'
            ];
        } else {
            throw new Exception('Failed to delete buffet package');
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
?>