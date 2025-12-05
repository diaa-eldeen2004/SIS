<?php
require_once __DIR__ . '/../models/User.php';

class StudentController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function list() {
        ob_clean();
        header('Content-Type: application/json');
        try {
            $filters = [];
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
            if (!empty($_GET['year'])) $filters['year'] = $_GET['year'];
            if (!empty($_GET['major'])) $filters['major'] = $_GET['major'];
            if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];

            $rows = $this->user->listStudents($filters);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function create() {
        ob_clean();
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                return;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input === null) $input = $_POST;

            // Basic validation
            $required = ['first_name','last_name','email'];
            foreach ($required as $f) {
                if (empty($input[$f])) {
                    http_response_code(400);
                    echo json_encode(['success'=>false,'message'=>"$f is required"]);
                    return;
                }
            }

            // Hash password (use provided or default)
            $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
            $input['password'] = $password;
            
            // Check if email already exists
            if ($this->user->emailExists($input['email'])) {
                http_response_code(409);
                echo json_encode(['success'=>false,'message'=>'Email already registered']);
                return;
            }

            $ok = $this->user->createStudent($input);
            if ($ok) echo json_encode(['success'=>true,'message'=>'Student created']);
            else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create student']); }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
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
        if (empty($input['id'])) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id is required']); return; }
        $id = (int)$input['id'];
        unset($input['id']);
        $ok = $this->user->updateStudent($id, $input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Student updated']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to update student']); }
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
        if (empty($input['id'])) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id is required']); return; }
        $id = (int)$input['id'];
        $ok = $this->user->deleteStudent($id);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Student deleted']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to delete student']); }
    }

    public function exportCsv() {
        $filters = [];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['year'])) $filters['year'] = $_GET['year'];
        $rows = $this->user->exportStudents($filters);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="students_export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Student Number','First Name','Last Name','Email','Major','Minor','GPA','Status','Created At']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'],$r['student_number'],$r['first_name'],$r['last_name'],$r['email'],$r['major'],$r['minor'],$r['gpa'],$r['status'],$r['created_at']]);
        }
        fclose($out);
        exit;
    }

    /**
     * Get a single student by ID
     */
    public function get() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid id']); return; }
        $stmt = $db->prepare("SELECT id, student_number, first_name, last_name, email, phone, year_enrolled, major, minor, gpa, status, created_at FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($student) echo json_encode(['success'=>true,'data'=>$student]);
        else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Student not found']); }
    }

    /**
     * Bulk insert students from CSV import
     */
    public function import() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success'=>false,'message'=>'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['students']) || !is_array($input['students'])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Invalid input']);
            return;
        }
        $ok = $this->user->bulkInsertStudents($input['students']);
        if ($ok) echo json_encode(['success'=>true,'message'=>count($input['students']).' students imported']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Import failed']); }
    }

    /**
     * Bulk update multiple students
     */
    public function bulkUpdate() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success'=>false,'message'=>'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['ids']) || !is_array($input['ids']) || empty($input['updates'])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Invalid input']);
            return;
        }
        $count = 0;
        foreach ($input['ids'] as $id) {
            if ($this->user->updateStudent((int)$id, $input['updates'])) {
                $count++;
            }
        }
        echo json_encode(['success'=>true,'message'=>"$count students updated"]);
    }
}
