<?php
require_once 'config/database.php';

class NotificationManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Notificatie aanmaken
    public function createNotification($userId, $title, $message, $type = 'info', $relatedId = null, $relatedType = null) {
        if (!$this->db->isConnected()) {
            return false;
        }
        
        $sql = "INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, ?, ?, ?, ?, ?)";
        $params = array($userId, $title, $message, $type, $relatedId, $relatedType);
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    // Notificaties ophalen voor gebruiker
    public function getUserNotifications($userId, $unreadOnly = false) {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = array($userId);
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Notificatie als gelezen markeren
    public function markAsRead($notificationId, $userId) {
        if (!$this->db->isConnected()) {
            return false;
        }
        
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $this->db->query($sql, array($notificationId, $userId));
        return true;
    }
    
    // Alle notificaties als gelezen markeren
    public function markAllAsRead($userId) {
        if (!$this->db->isConnected()) {
            return false;
        }
        
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
        $this->db->query($sql, array($userId));
        return true;
    }
    
    // Notificatie verwijderen
    public function deleteNotification($notificationId, $userId) {
        if (!$this->db->isConnected()) {
            return false;
        }
        
        $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
        $this->db->query($sql, array($notificationId, $userId));
        return true;
    }
    
    // Aantal ongelezen notificaties
    public function getUnreadCount($userId) {
        if (!$this->db->isConnected()) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $result = $this->db->fetchOne($sql, array($userId));
        return $result['count'];
    }
    
    // Notificatie voor nieuwe wereldwonder goedkeuring
    public function notifyWorldWonderApproval($worldWonderId, $worldWonderName, $approvedBy) {
        // Notify redacteurs and archivists
        $sql = "SELECT id FROM users WHERE role_id IN (3, 4) AND is_active = 1";
        $users = $this->db->fetchAll($sql);
        
        foreach ($users as $user) {
            $this->createNotification(
                $user['id'],
                'Wereldwonder goedgekeurd',
                "Het wereldwonder '{$worldWonderName}' is goedgekeurd door {$approvedBy}",
                'success',
                $worldWonderId,
                'world_wonder'
            );
        }
    }
    
    // Notificatie voor nieuwe media upload
    public function notifyMediaUpload($worldWonderId, $worldWonderName, $uploadedBy) {
        // Notify redacteurs
        $sql = "SELECT id FROM users WHERE role_id = 3 AND is_active = 1";
        $users = $this->db->fetchAll($sql);
        
        foreach ($users as $user) {
            $this->createNotification(
                $user['id'],
                'Nieuwe media upload',
                "Er is nieuwe media geüpload voor '{$worldWonderName}' door {$uploadedBy}",
                'info',
                $worldWonderId,
                'world_wonder'
            );
        }
    }
    
    // Notificatie voor nieuwe wereldwonder aanmelding
    public function notifyNewWorldWonder($worldWonderId, $worldWonderName, $createdBy) {
        // Notify redacteurs
        $sql = "SELECT id FROM users WHERE role_id = 3 AND is_active = 1";
        $users = $this->db->fetchAll($sql);
        
        foreach ($users as $user) {
            $this->createNotification(
                $user['id'],
                'Nieuw wereldwonder aangemeld',
                "Er is een nieuw wereldwonder aangemeld: '{$worldWonderName}' door {$createdBy}",
                'info',
                $worldWonderId,
                'world_wonder'
            );
        }
    }
    
    // Database connectie sluiten
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
