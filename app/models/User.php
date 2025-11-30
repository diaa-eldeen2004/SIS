<?php

require_once __DIR__ . '/../core/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * List students with optional filters: search (name/email), year, major, status
     */
    public function listStudents($filters = []) {
        $sql = "SELECT id, student_number, first_name, last_name, email, major, minor, gpa, status, last_activity, created_at FROM users WHERE role = 'student'";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_number LIKE ? )";
            $like = "%" . $filters['search'] . "%";
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(created_at) = ?";
            $params[] = $filters['year'];
        }
        if (!empty($filters['major'])) {
            $sql .= " AND major = ?";
            $params[] = $filters['major'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a student (admin action)
     */
    public function createStudent($data) {
        $stmt = $this->db->prepare("INSERT INTO users (student_number, first_name, last_name, email, password, role, gpa, major, minor, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'student', ?, ?, ?, ?, NOW(), NOW())");
        // password should be hashed before calling this method
        return $stmt->execute([
            $data['student_number'] ?? null,
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['email'] ?? null,
            $data['password'] ?? null,
            $data['gpa'] ?? null,
            $data['major'] ?? null,
            $data['minor'] ?? null,
            $data['status'] ?? 'not_active'
        ]);
    }

    /**
     * Update a student by id
     */
    public function updateStudent($id, $data) {
        $fields = [];
        $params = [];
        $allowed = ['student_number','first_name','last_name','email','gpa','major','minor','status','last_activity'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a student by id
     */
    public function deleteStudent($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        return $stmt->execute([$id]);
    }

    /**
     * Bulk insert students from array of associative rows. Each row must include first_name,last_name,email,password (plain) optionally student_number,gpa,major,minor,status
     */
    public function bulkInsertStudents(array $rows) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO users (student_number, first_name, last_name, email, password, role, gpa, major, minor, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'student', ?, ?, ?, ?, NOW(), NOW())");
            foreach ($rows as $r) {
                $pwd = isset($r['password']) ? password_hash($r['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
                $stmt->execute([
                    $r['student_number'] ?? null,
                    $r['first_name'] ?? null,
                    $r['last_name'] ?? null,
                    $r['email'] ?? null,
                    $pwd,
                    $r['gpa'] ?? null,
                    $r['major'] ?? null,
                    $r['minor'] ?? null,
                    $r['status'] ?? 'not_active'
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Bulk insert error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Export students according to filters — returns an array of rows
     */
    public function exportStudents($filters = []) {
        return $this->listStudents($filters);
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

