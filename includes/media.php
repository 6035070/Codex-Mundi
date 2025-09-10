<?php
require_once 'config/database.php';

class MediaManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Media uploaden
    public function uploadMedia($worldWonderId, $file, $uploadedBy, $description = '') {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $uploadDir = 'uploads/world_wonders/';
        
        // Upload directory aanmaken als deze niet bestaat
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Bestandsvalidatie
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Alleen JPEG, PNG, GIF, WebP, PDF en Word documenten zijn toegestaan.');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Bestand is te groot. Maximum grootte is 10MB.');
        }
        
        // Unieke bestandsnaam genereren
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Bestand verplaatsen
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Media record aanmaken
            $sql = "INSERT INTO media (world_wonder_id, filename, original_name, file_path, file_type, file_size, uploaded_by, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array(
                $worldWonderId,
                $filename,
                $file['name'],
                $filepath,
                $file['type'],
                $file['size'],
                $uploadedBy,
                $description
            );
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } else {
            throw new Exception('Er is een fout opgetreden bij het uploaden van het bestand.');
        }
    }
    
    // Media ophalen op basis van wereldwonder ID
    public function getMediaByWorldWonderId($worldWonderId) {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT m.*, u.username as uploaded_by_username 
                FROM media m 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                WHERE m.world_wonder_id = ? 
                ORDER BY m.is_primary DESC, m.created_at ASC";
        return $this->db->fetchAll($sql, array($worldWonderId));
    }
    
    // Media ophalen op basis van ID
    public function getMediaById($id) {
        if (!$this->db->isConnected()) {
            return null;
        }
        
        $sql = "SELECT m.*, u.username as uploaded_by_username 
                FROM media m 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                WHERE m.id = ?";
        return $this->db->fetchOne($sql, array($id));
    }
    
    // Media goedkeuren
    public function approveMedia($id, $approvedBy) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $sql = "UPDATE media SET is_approved = 1, approved_by = ? WHERE id = ?";
        $this->db->query($sql, array($approvedBy, $id));
        return true;
    }
    
    // Media verwijderen
    public function deleteMedia($id) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Haal media info op
        $media = $this->getMediaById($id);
        if (!$media) {
            throw new Exception("Media niet gevonden");
        }
        
        // Verwijder bestand
        if (file_exists($media['file_path'])) {
            unlink($media['file_path']);
        }
        
        // Verwijder database record
        $sql = "DELETE FROM media WHERE id = ?";
        $this->db->query($sql, array($id));
        return true;
    }
    
    // Media als primair instellen
    public function setPrimaryMedia($worldWonderId, $mediaId) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Eerst alle media van dit wereldwonder als niet-primair instellen
        $sql = "UPDATE media SET is_primary = 0 WHERE world_wonder_id = ?";
        $this->db->query($sql, array($worldWonderId));
        
        // Dan de geselecteerde media als primair instellen
        $sql = "UPDATE media SET is_primary = 1 WHERE id = ? AND world_wonder_id = ?";
        $this->db->query($sql, array($mediaId, $worldWonderId));
        return true;
    }
    
    // Media bijwerken
    public function updateMedia($id, $data) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $fields = array();
        $params = array();
        
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }
        
        if (isset($data['is_primary'])) {
            $fields[] = "is_primary = ?";
            $params[] = $data['is_primary'];
        }
        
        if (empty($fields)) {
            return true;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE media SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        return true;
    }
    
    // Alle media ophalen (voor beheer)
    public function getAllMedia($filters = array()) {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT m.*, w.name as world_wonder_name, u.username as uploaded_by_username 
                FROM media m 
                LEFT JOIN world_wonders w ON m.world_wonder_id = w.id 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                WHERE 1=1";
        $params = array();
        
        if (isset($filters['is_approved']) && $filters['is_approved'] !== null) {
            $sql .= " AND m.is_approved = ?";
            $params[] = $filters['is_approved'];
        }
        
        if (isset($filters['file_type']) && !empty($filters['file_type'])) {
            $sql .= " AND m.file_type LIKE ?";
            $params[] = '%' . $filters['file_type'] . '%';
        }
        
        $sql .= " ORDER BY m.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Database connectie sluiten
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
