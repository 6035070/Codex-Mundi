
<?php
// Database configuratie voor SQL Server
class Database {
    private $server = "localhost";
    private $database = "ImageCRUD";
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
                $errors = sqlsrv_errors();
                throw new Exception("Database connection failed: " . print_r($errors, true));
            }
        } catch (Exception $e) {
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
        
        $stmt = sqlsrv_query($this->connection, $sql, $params);
        
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            throw new Exception("Query failed: " . print_r($errors, true));
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
