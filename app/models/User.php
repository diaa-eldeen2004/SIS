<?php

require_once __DIR__ . '/../core/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Check if email already exists
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Create a new user
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (first_name, last_name, email, password, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['password'],
            $data['role'] ?? 'user'
        ]);
    }

    /**
     * Get user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, role, created_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Get user by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Generate and store password reset code
     */
    public function generateResetCode($email) {
        // Generate a 6-digit numeric reset code
        $resetCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        // Set expiration to 30 minutes from now
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_reset_code = ?, password_reset_expires = ? 
            WHERE email = ?
        ");
        
        $result = $stmt->execute([$resetCode, $expiresAt, $email]);
        
        if ($result) {
            return $resetCode;
        }
        return false;
    }

    /**
     * Verify password reset code
     */
    public function verifyResetCode($email, $code) {
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE email = ? 
            AND password_reset_code = ? 
            AND password_reset_expires > NOW()
        ");
        $stmt->execute([$email, $code]);
        return $stmt->fetch() !== false;
    }

    /**
     * Update user password
     */
    public function updatePassword($email, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = ?, password_reset_code = NULL, password_reset_expires = NULL 
            WHERE email = ?
        ");
        
        return $stmt->execute([$hashedPassword, $email]);
    }

    /**
     * Clear reset code for an email
     */
    public function clearResetCode($email) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_reset_code = NULL, password_reset_expires = NULL 
            WHERE email = ?
        ");
        
        return $stmt->execute([$email]);
    }
}

