<?php
require_once 'config/database.php';

class ItemManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Alle items ophalen
    public function getAllItems() {
        $sql = "SELECT * FROM items ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    // Item ophalen op basis van ID
    public function getItemById($id) {
        $sql = "SELECT * FROM items WHERE id = ?";
        return $this->db->fetchOne($sql, array($id));
    }
    
    // Nieuw item toevoegen
    public function createItem($title, $description, $imagePath = null) {
        $sql = "INSERT INTO items (title, description, image_path) VALUES (?, ?, ?)";
        $params = array($title, $description, $imagePath);
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    // Item bijwerken
    public function updateItem($id, $title, $description, $imagePath = null) {
        $sql = "UPDATE items SET title = ?, description = ?, image_path = ?, updated_at = GETDATE() WHERE id = ?";
        $params = array($title, $description, $imagePath, $id);
        
        $this->db->query($sql, $params);
        return true;
    }
    
    // Item verwijderen
    public function deleteItem($id) {
        // Eerst het item ophalen om het pad van de afbeelding te krijgen
        $item = $this->getItemById($id);
        
        if ($item && $item['image_path'] && file_exists($item['image_path'])) {
            unlink($item['image_path']); // Afbeelding verwijderen van de server
        }
        
        $sql = "DELETE FROM items WHERE id = ?";
        $this->db->query($sql, array($id));
        return true;
    }
    
    // Afbeelding uploaden
    public function uploadImage($file) {
        $uploadDir = 'uploads/';
        
        // Upload directory aanmaken als deze niet bestaat
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Bestandsvalidatie
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Alleen JPEG, PNG, GIF en WebP afbeeldingen zijn toegestaan.');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Afbeelding is te groot. Maximum grootte is 5MB.');
        }
        
        // Unieke bestandsnaam genereren
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Bestand verplaatsen
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        } else {
            throw new Exception('Er is een fout opgetreden bij het uploaden van de afbeelding.');
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
