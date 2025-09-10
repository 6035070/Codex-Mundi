<?php
require_once 'config/database.php';

class MediaManager {
    private $db;
    private $uploadPath = 'uploads/';
    private $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain');
    private $maxFileSize = 5242880; // 5MB
    
    public function __construct() {
        $this->db = new Database();
        $this->createUploadDirectory();
    }
    
    private function createUploadDirectory() {
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    // Upload media file
    public function uploadMedia($worldWonderId, $file, $uploadedBy, $description = '') {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Validate file
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception("Invalid file upload");
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed");
        }
        
        // Check file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception("File type not allowed");
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File too large");
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filePath = $this->uploadPath . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception("Failed to save file");
        }
        
        // Save to database
        $sql = "INSERT INTO media (world_wonder_id, filename, original_name, file_path, file_type, file_size, uploaded_by, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array(
            $worldWonderId,
            $filename,
            $file['name'],
            $filePath,
            $mimeType,
            $file['size'],
            $uploadedBy,
            $description
        );
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    // Get media by world wonder ID
    public function getMediaByWorldWonderId($worldWonderId, $approvedOnly = true) {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT m.*, u.username as uploaded_by_username 
                FROM media m 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                WHERE m.world_wonder_id = ?";
        $params = array($worldWonderId);
        
        if ($approvedOnly) {
            $sql .= " AND m.is_approved = 1";
        }
        
        $sql .= " ORDER BY m.is_primary DESC, m.created_at ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Approve media
    public function approveMedia($mediaId, $approvedBy) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $sql = "UPDATE media SET is_approved = 1, approved_by = ? WHERE id = ?";
        $this->db->query($sql, array($approvedBy, $mediaId));
        return true;
    }
    
    // Set primary image
    public function setPrimaryImage($mediaId, $worldWonderId) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Remove primary from all other images of this world wonder
        $sql = "UPDATE media SET is_primary = 0 WHERE world_wonder_id = ?";
        $this->db->query($sql, array($worldWonderId));
        
        // Set this image as primary
        $sql = "UPDATE media SET is_primary = 1 WHERE id = ? AND world_wonder_id = ?";
        $this->db->query($sql, array($mediaId, $worldWonderId));
        
        return true;
    }
    
    // Delete media
    public function deleteMedia($mediaId) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Get file path
        $sql = "SELECT file_path FROM media WHERE id = ?";
        $media = $this->db->fetchOne($sql, array($mediaId));
        
        if ($media) {
            // Delete physical file
            if (file_exists($media['file_path'])) {
                unlink($media['file_path']);
            }
            
            // Delete database record
            $sql = "DELETE FROM media WHERE id = ?";
            $this->db->query($sql, array($mediaId));
        }
        
        return true;
    }
    
    // Get pending media for approval
    public function getPendingMedia() {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT m.*, u.username as uploaded_by_username, w.name as world_wonder_name
                FROM media m 
                LEFT JOIN users u ON m.uploaded_by = u.id 
                LEFT JOIN world_wonders w ON m.world_wonder_id = w.id
                WHERE m.is_approved = 0 
                ORDER BY m.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    // Update file restrictions
    public function updateFileRestrictions($allowedTypes, $maxFileSize) {
        $this->allowedTypes = $allowedTypes;
        $this->maxFileSize = $maxFileSize;
        return true;
    }
    
    // Get file restrictions
    public function getFileRestrictions() {
        return array(
            'allowed_types' => $this->allowedTypes,
            'max_file_size' => $this->maxFileSize
        );
    }
    
    // Database connectie sluiten
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>