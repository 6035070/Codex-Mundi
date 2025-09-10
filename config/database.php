<?php
// Database configuratie voor Codex Mundi
class Database {
    private $host = "localhost";
    private $database = "codex_mundi";
    private $username = "root";
    private $password = "";
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            // MySQL connection string
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Log error maar stop niet de hele applicatie
            error_log("Database connection error: " . $e->getMessage());
            $this->connection = null;
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function isConnected() {
        return $this->connection !== null;
    }
    
    public function query($sql, $params = array()) {
        if (!$this->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = array()) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function fetchOne($sql, $params = array()) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function execute($sql, $params = array()) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction() {
        if ($this->isConnected()) {
            $this->connection->beginTransaction();
        }
    }
    
    public function commit() {
        if ($this->isConnected()) {
            $this->connection->commit();
        }
    }
    
    public function rollback() {
        if ($this->isConnected()) {
            $this->connection->rollback();
        }
    }
    
    public function close() {
        $this->connection = null;
    }
}
?>
