<?php
require_once 'config/database.php';

class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Gebruiker aanmaken
    public function createUser($username, $email, $password, $firstName, $lastName, $roleId) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Controleer of gebruikersnaam en email al bestaan
        $existingUser = $this->getUserByUsername($username);
        if ($existingUser) {
            throw new Exception("Gebruikersnaam bestaat al");
        }
        
        $existingEmail = $this->getUserByEmail($email);
        if ($existingEmail) {
            throw new Exception("Email adres bestaat al");
        }
        
        // Hash wachtwoord
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role_id) VALUES (?, ?, ?, ?, ?, ?)";
        $params = array($username, $email, $passwordHash, $firstName, $lastName, $roleId);
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    // Gebruiker ophalen op basis van ID
    public function getUserById($id) {
        if (!$this->db->isConnected()) {
            return null;
        }
        
        $sql = "SELECT u.*, r.name as role_name, r.permissions FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ? AND u.is_active = 1";
        return $this->db->fetchOne($sql, array($id));
    }
    
    // Gebruiker ophalen op basis van gebruikersnaam
    public function getUserByUsername($username) {
        if (!$this->db->isConnected()) {
            return null;
        }
        
        $sql = "SELECT u.*, r.name as role_name, r.permissions FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.username = ? AND u.is_active = 1";
        return $this->db->fetchOne($sql, array($username));
    }
    
    // Gebruiker ophalen op basis van email
    public function getUserByEmail($email) {
        if (!$this->db->isConnected()) {
            return null;
        }
        
        $sql = "SELECT u.*, r.name as role_name, r.permissions FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.email = ? AND u.is_active = 1";
        return $this->db->fetchOne($sql, array($email));
    }
    
    // Gebruiker authenticeren
    public function authenticate($username, $password) {
        $user = $this->getUserByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    }
    
    // Alle gebruikers ophalen
    public function getAllUsers() {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT u.*, r.name as role_name FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.is_active = 1 
                ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    // Gebruiker bijwerken
    public function updateUser($id, $data) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $fields = array();
        $params = array();
        
        if (isset($data['first_name'])) {
            $fields[] = "first_name = ?";
            $params[] = $data['first_name'];
        }
        
        if (isset($data['last_name'])) {
            $fields[] = "last_name = ?";
            $params[] = $data['last_name'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['role_id'])) {
            $fields[] = "role_id = ?";
            $params[] = $data['role_id'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $params[] = $data['is_active'];
        }
        
        if (empty($fields)) {
            return true;
        }
        
        $fields[] = "updated_at = GETDATE()";
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        return true;
    }
    
    // Gebruiker verwijderen (soft delete)
    public function deleteUser($id) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        $sql = "UPDATE users SET is_active = 0, updated_at = GETDATE() WHERE id = ?";
        $this->db->query($sql, array($id));
        return true;
    }
    
    // Controleer of gebruiker een bepaalde rol heeft
    public function hasRole($userId, $roleName) {
        $user = $this->getUserById($userId);
        return $user && $user['role_name'] === $roleName;
    }
    
    // Controleer of gebruiker een bepaalde permission heeft
    public function hasPermission($userId, $permission) {
        $user = $this->getUserById($userId);
        if (!$user || !$user['permissions']) {
            return false;
        }
        
        $permissions = json_decode($user['permissions'], true);
        return isset($permissions[$permission]) && $permissions[$permission] === true;
    }
    
    // Alle rollen ophalen
    public function getAllRoles() {
        if (!$this->db->isConnected()) {
            return array();
        }
        
        $sql = "SELECT * FROM roles ORDER BY name";
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
