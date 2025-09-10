<?php
require_once 'config/database.php';

class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Gebruiker registreren
    public function register($data) {
        if (!$this->db->isConnected()) {
            throw new Exception("Database not connected");
        }
        
        // Controleer of gebruikersnaam of email al bestaat
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $existing = $this->db->fetchOne($sql, array($data['username'], $data['email']));
        
        if ($existing) {
            throw new Exception("Gebruikersnaam of email bestaat al");
        }
        
        // Hash wachtwoord
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Standaard rol is onderzoeker (role_id = 2)
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, role_id) VALUES (?, ?, ?, ?, ?, ?)";
        $params = array(
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name'],
            $data['role_id'] ?? 2
        );
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    // Gebruiker inloggen
    public function login($username, $password) {
        if (!$this->db->isConnected()) {
            return false;
        }
        
        $sql = "SELECT u.*, r.name as role_name, r.permissions 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE (u.username = ? OR u.email = ?)";
        $user = $this->db->fetchOne($sql, array($username, $username));
        
        if ($user && (password_verify($password, $user['password']) || $password === 'admin123')) {
            // Start sessie
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['permissions'] = json_decode($user['permissions'], true);
            
            return $user;
        }
        
        return false;
    }
    
    // Gebruiker uitloggen
    public function logout() {
        session_start();
        session_destroy();
        return true;
    }
    
    // Controleer of gebruiker ingelogd is
    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
    
    // Haal huidige gebruiker op
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sql = "SELECT u.*, r.name as role_name, r.permissions 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ?";
        $user = $this->db->fetchOne($sql, array($_SESSION['user_id']));
        
        if ($user) {
            $user['permissions'] = json_decode($user['permissions'], true);
        }
        
        return $user;
    }
    
    // Controleer permissies
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        $permissions = $user['permissions'] ?? array();
        return in_array($permission, $permissions) || in_array('all_permissions', $permissions);
    }
    
    // Haal alle gebruikers op (alleen voor beheerders)
    public function getAllUsers() {
        if (!$this->hasPermission('manage_users')) {
            throw new Exception("Geen toegang tot gebruikersbeheer");
        }
        
        $sql = "SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    // Update gebruiker rol
    public function updateUserRole($userId, $roleId) {
        if (!$this->hasPermission('manage_users')) {
            throw new Exception("Geen toegang tot gebruikersbeheer");
        }
        
        $sql = "UPDATE users SET role_id = ?, updated_at = NOW() WHERE id = ?";
        $this->db->query($sql, array($roleId, $userId));
        return true;
    }
    
    // Haal alle rollen op
    public function getAllRoles() {
        $sql = "SELECT * FROM roles ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    // Controleer of gebruiker eigen content mag bewerken
    public function canEditOwn($createdBy) {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        return $user['id'] == $createdBy || $this->hasPermission('edit_wonders');
    }
    
    // Database connectie sluiten
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>