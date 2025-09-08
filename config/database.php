<?php
// Database configuratie voor SQL Server
class Database {
    private $server = "localhost"; // of je SQL Server instance naam
    private $database = "ImageCRUD";
    private $username = "JOELLAPTOP\\Joell"; // Windows Authentication gebruiker
    private $password = ""; // Leeg voor Windows Authentication
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            // SQL Server connection string voor Windows Authentication
            $connectionInfo = array(
                "Database" => $this->database,
                "CharacterSet" => "UTF-8",
                "TrustServerCertificate" => true
            );
            
            $this->connection = sqlsrv_connect($this->server, $connectionInfo);
            
            if ($this->connection === false) {
                throw new Exception("Database connection failed: " . print_r(sqlsrv_errors(), true));
            }
        } catch (Exception $e) {
            die("Connection error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = array()) {
        $stmt = sqlsrv_query($this->connection, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception("Query failed: " . print_r(sqlsrv_errors(), true));
        }
        
        return $stmt;
    }
    
    public function fetchAll($sql, $params = array()) {
        $stmt = $this->query($sql, $params);
        $results = array();
        
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        
        return $results;
    }
    
    public function fetchOne($sql, $params = array()) {
        $stmt = $this->query($sql, $params);
        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
    
    public function lastInsertId() {
        $stmt = $this->query("SELECT SCOPE_IDENTITY() as id");
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['id'];
    }
    
    public function close() {
        if ($this->connection) {
            sqlsrv_close($this->connection);
        }
    }
}
?>
