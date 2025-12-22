<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Load from environment variables with fallback to defaults
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'student_management_system';
        $this->username = getenv('DB_USER') ?: 'root';
        // For security, require password to be explicitly set (even if empty)
        $dbPass = getenv('DB_PASS');
        $this->password = ($dbPass !== false) ? $dbPass : '';
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            // Log error instead of displaying it
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Show user-friendly error
            if (getenv('APP_ENV') === 'development') {
                die("Database Connection Error: " . $e->getMessage());
            } else {
                die("Database connection failed. Please contact the administrator.");
            }
        }
        
        return $this->conn;
    }
}
?>