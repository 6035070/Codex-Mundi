<?php
require_once 'config/database.php';

class ActivityLogManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Activiteit loggen
    public function logActivity($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        if (!$this->db->isConnected()) {
            return false;
        }
        
        $sql = "INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array(
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $this->getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );
        
        $this->db->query($sql, $params);
        return true;
    }
    
    // Activiteiten ophalen
    public function getActivityLogs($filters = array()) {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT al.*, u.username, u.first_name, u.last_name 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE 1=1";
        $params = array();
        
        if (isset($filters['user_id']) && !empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['action']) && !empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (isset($filters['table_name']) && !empty($filters['table_name'])) {
            $sql .= " AND al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY al.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";
            $params[] = $filters['limit'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Recente activiteiten ophalen
    public function getRecentActivity($limit = 10) {
        return $this->getActivityLogs(array('limit' => $limit));
    }
    
    // Activiteiten per gebruiker
    public function getUserActivity($userId, $limit = 20) {
        return $this->getActivityLogs(array('user_id' => $userId, 'limit' => $limit));
    }
    
    // Client IP adres ophalen
    private function getClientIP() {
        $ipKeys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    // Database connectie sluiten
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
