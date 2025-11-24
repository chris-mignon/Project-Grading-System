<?php
class Database {
    private $host = "mysql-1c358fde-dspmosfet-d487.e.aivencloud.com";
    private $db_name = "project_evaluation";
    private $username = "avnadmin";
    private $password = "AVNS_1cMd-sZCDVlCDBsu2Gn";
    private $port = "18718";   
    public $conn;

    public function getConnection() {
    if ($this->conn !== null) {
        return $this->conn; // Return existing connection if available
    }
    
    try {
        $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false, // Set to true for persistent connections if needed
        ];
        
        $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        
        // Additional configuration
        $this->conn->exec("SET time_zone = '+00:00';");
        
        return $this->conn;
        
    } catch(PDOException $exception) {
        // Log error instead of echoing (better for production)
        error_log("Database connection error: " . $exception->getMessage());
        
        // Throw exception or return false based on your error handling strategy
        throw new RuntimeException("Database connection failed: " . $exception->getMessage());
        // Or: return false;
    }
}
}
?>