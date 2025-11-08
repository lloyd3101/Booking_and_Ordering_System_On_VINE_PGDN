<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$host = 'localhost';
$dbname = 'restobar';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
}

class DatabaseConfig {
    public static $host = 'localhost';
    public static $username = 'your_username';
    public static $password = 'your_password';
    public static $database = 'restobar_db';
    public static $charset = 'utf8mb4';
}

// Database connection class
class Database {
    private $connection;
    
    public function __construct() {
        try {
            $dsn = "mysql:host=" . DatabaseConfig::$host . 
                   ";dbname=" . DatabaseConfig::$database . 
                   ";charset=" . DatabaseConfig::$charset;
            
            $this->connection = new PDO($dsn, DatabaseConfig::$username, DatabaseConfig::$password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

?>