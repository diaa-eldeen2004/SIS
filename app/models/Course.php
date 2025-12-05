<?php

require_once __DIR__ . '/../core/Database.php';

class Course {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO courses (course_code, course_name, description, department, credits, level, doctor_id, max_students, status, semester, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            return $stmt->execute([
                $data['course_code'] ?? '',
                $data['course_name'] ?? '',
                $data['description'] ?? null,
                $data['department'] ?? '',
                $data['credits'] ?? 3,
                $data['level'] ?? null,
                $data['doctor_id'] ?? null,
                $data['max_students'] ?? 50,
                $data['status'] ?? 'active',
                $data['semester'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Course create error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $values = [];
            
            $allowedFields = ['course_code', 'course_name', 'description', 'department', 'credits', 'level', 'doctor_id', 'max_students', 'status', 'semester'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $fields[] = "updated_at = NOW()";
            $values[] = $id;
            
            $sql = "UPDATE courses SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Course update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Course delete error: " . $e->getMessage());
            return false;
        }
    }
}

