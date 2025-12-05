<?php
require_once __DIR__ . '/../models/Course.php';

class CourseController {
    private $course;

    public function __construct() {
        $this->course = new Course();
    }

    public function list() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        
        $search = $_GET['search'] ?? '';
        $department = $_GET['department'] ?? '';
        $level = $_GET['level'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (course_code LIKE ? OR course_name LIKE ? OR description LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        
        if ($department) {
            $where .= " AND department = ?";
            $params[] = $department;
        }
        
        if ($level) {
            $where .= " AND level = ?";
            $params[] = (int)$level;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        try {
            $stmt = $db->prepare("
                SELECT c.id, c.course_code, c.course_name, c.description, c.department, 
                       c.level, c.credits, c.status, c.max_students, c.created_at,
                       c.doctor_id, d.first_name as doctor_first_name, d.last_name as doctor_last_name,
                       (SELECT COUNT(*) FROM student_courses sc WHERE sc.course_id = c.id) as student_count
                FROM courses c
                LEFT JOIN doctors d ON c.doctor_id = d.id
                $where
                ORDER BY c.created_at DESC
                LIMIT 100
            ");
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function get() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { 
            http_response_code(400); 
            echo json_encode(['success'=>false,'message'=>'Invalid id']); 
            return; 
        }
        try {
            $stmt = $db->prepare("
                SELECT c.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
                FROM courses c
                LEFT JOIN doctors d ON c.doctor_id = d.id
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($course) echo json_encode(['success'=>true,'data'=>$course]);
            else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Course not found']); }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function create() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;

        $required = ['course_code','course_name','department'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>"$f is required"]);
                return;
            }
        }

        $ok = $this->course->create($input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Course created']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create course']); }
    }

    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;
        if (empty($input['id'])) { 
            http_response_code(400); 
            echo json_encode(['success'=>false,'message'=>'id is required']); 
            return; 
        }
        $id = (int)$input['id'];
        unset($input['id']);
        $ok = $this->course->update($id, $input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Course updated']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to update course']); }
    }

    public function delete() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;
        if (empty($input['id'])) { 
            http_response_code(400); 
            echo json_encode(['success'=>false,'message'=>'id is required']); 
            return; 
        }
        $id = (int)$input['id'];
        $ok = $this->course->delete($id);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Course deleted']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to delete course']); }
    }
}

