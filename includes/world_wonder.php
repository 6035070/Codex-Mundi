<?php
require_once 'config/database.php';

class WorldWonderManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Wereldwonder aanmaken
    public function createWorldWonder($data) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $sql = "INSERT INTO world_wonders (name, description, historical_info, construction_year, status, category, continent, country, city, latitude, longitude, created_by, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array(
            $data['name'],
            $data['description'],
            $data['historical_info'],
            $data['construction_year'],
            $data['status'],
            $data['category'],
            $data['continent'],
            $data['country'],
            $data['city'],
            $data['latitude'],
            $data['longitude'],
            $data['created_by'],
            $data['is_approved'] ?? 0
        );
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    // Wereldwonder ophalen op basis van ID
    public function getWorldWonderById($id) {
        if (!$this->db->isConnected()) {
            return null;
        }
        
        $sql = "SELECT w.*, u.username as created_by_username, u.first_name, u.last_name,
                       a.username as approved_by_username
                FROM world_wonders w
                LEFT JOIN users u ON w.created_by = u.id
                LEFT JOIN users a ON w.approved_by = a.id
                WHERE w.id = ?";
        return $this->db->fetchOne($sql, array($id));
    }
    
    // Alle wereldwonderen ophalen met filters
    public function getAllWorldWonders($filters = array()) {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT w.*, u.username as created_by_username, u.first_name, u.last_name,
                       a.username as approved_by_username
                FROM world_wonders w
                LEFT JOIN users u ON w.created_by = u.id
                LEFT JOIN users a ON w.approved_by = a.id
                WHERE 1=1";
        $params = array();
        
        // Filters toepassen
        if (isset($filters['category']) && !empty($filters['category'])) {
            $sql .= " AND w.category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['continent']) && !empty($filters['continent'])) {
            $sql .= " AND w.continent = ?";
            $params[] = $filters['continent'];
        }
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['is_approved']) && $filters['is_approved'] !== null) {
            $sql .= " AND w.is_approved = ?";
            $params[] = $filters['is_approved'];
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (w.name LIKE ? OR w.description LIKE ? OR w.country LIKE ? OR w.city LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Sortering
        $orderBy = "w.created_at DESC";
        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'name_asc':
                    $orderBy = "w.name ASC";
                    break;
                case 'name_desc':
                    $orderBy = "w.name DESC";
                    break;
                case 'year_asc':
                    $orderBy = "w.construction_year ASC";
                    break;
                case 'year_desc':
                    $orderBy = "w.construction_year DESC";
                    break;
                case 'updated_desc':
                    $orderBy = "w.updated_at DESC";
                    break;
            }
        }
        
        $sql .= " ORDER BY " . $orderBy;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Wereldwonder bijwerken
    public function updateWorldWonder($id, $data) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $fields = array();
        $params = array();
        
        $allowedFields = array('name', 'description', 'historical_info', 'construction_year', 'status', 'category', 'continent', 'country', 'city', 'latitude', 'longitude', 'is_approved', 'approved_by');
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return true;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE world_wonders SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        return true;
    }
    
    // Wereldwonder verwijderen
    public function deleteWorldWonder($id) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Verwijder eerst alle media
        $mediaManager = new MediaManager();
        $media = $mediaManager->getMediaByWorldWonderId($id);
        foreach ($media as $m) {
            if (file_exists($m['file_path'])) {
                unlink($m['file_path']);
            }
        }
        
        $sql = "DELETE FROM world_wonders WHERE id = ?";
        $this->db->query($sql, array($id));
        return true;
    }
    
    // Wereldwonder goedkeuren
    public function approveWorldWonder($id, $approvedBy) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $sql = "UPDATE world_wonders SET is_approved = 1, approved_by = ?, updated_at = NOW() WHERE id = ?";
        $this->db->query($sql, array($approvedBy, $id));
        return true;
    }
    
    // Statistieken ophalen
    public function getStatistics() {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $stats = array();
        
        // Totaal aantal wereldwonderen
        $sql = "SELECT COUNT(*) as total FROM world_wonders";
        $result = $this->db->fetchOne($sql);
        $stats['total_wonders'] = $result['total'];
        
        // Per categorie
        $sql = "SELECT category, COUNT(*) as count FROM world_wonders GROUP BY category";
        $stats['by_category'] = $this->db->fetchAll($sql);
        
        // Per continent
        $sql = "SELECT continent, COUNT(*) as count FROM world_wonders GROUP BY continent";
        $stats['by_continent'] = $this->db->fetchAll($sql);
        
        // Per status
        $sql = "SELECT status, COUNT(*) as count FROM world_wonders GROUP BY status";
        $stats['by_status'] = $this->db->fetchAll($sql);
        
        // Meest recente
        $sql = "SELECT w.*, u.username as created_by_username 
                FROM world_wonders w 
                LEFT JOIN users u ON w.created_by = u.id 
                ORDER BY w.updated_at DESC 
                LIMIT 5";
        $stats['recent'] = $this->db->fetchAll($sql);
        
        return $stats;
    }
    
    // Wereldwonderen met GPS coördinaten ophalen voor kaart
    public function getWorldWondersForMap() {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT id, name, latitude, longitude, status, category 
                FROM world_wonders 
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND is_approved = 1";
        return $this->db->fetchAll($sql);
    }
    
    // Database connectie sluiten
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
