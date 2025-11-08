<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Get buffet packages
    $stmt = $pdo->query("SELECT * FROM buffet_packages WHERE is_active = 1");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get rates for each package
    foreach ($packages as &$package) {
        $stmt = $pdo->prepare("SELECT persons, rate FROM buffet_rates WHERE package_id = ? ORDER BY persons");
        $stmt->execute([$package['id']]);
        $package['rates'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'buffet_packages' => $packages
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch buffet packages: ' . $e->getMessage()
    ]);
}
?>