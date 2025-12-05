<?php
require_once __DIR__ . '/../models/User.php';

class DoctorController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function list() {
        ob_clean();
        header('Content-Type: application/json');
        try {
            require_once __DIR__ . '/../core/Database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, department, bio, created_at FROM doctors ORDER BY created_at DESC LIMIT 100");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

            // Validate required fields
            $required = ['first_name','last_name','email'];
            foreach ($required as $f) {
                if (empty($input[$f])) {
                    http_response_code(400);
                    echo json_encode(['success'=>false,'message'=>ucfirst(str_replace('_', ' ', $f)) . ' is required']);
                    return;
                }
            }

            // Validate email format
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>'Invalid email format']);
                return;
            }

            // Check if email already exists
            if ($this->user->emailExists($input['email'])) {
                http_response_code(409);
                echo json_encode(['success'=>false,'message'=>'Email already exists']);
                return;
            }

            // Hash password (use provided or default)
            $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
            $input['password'] = $password;

            // Ensure department has a default value if not provided
            if (empty($input['department'])) {
                $input['department'] = 'General';
            }

            // Create doctor
            $ok = $this->user->createDoctor($input);
            if ($ok) {
                echo json_encode(['success'=>true,'message'=>'Doctor created successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success'=>false,'message'=>'Failed to create doctor']);
            }
        } catch (PDOException $e) {
            ob_clean();
            http_response_code(500);
            $errorInfo = $e->errorInfo ?? [];
            error_log('Create doctor PDO error: ' . $e->getMessage());
            error_log('PDO Error Info: ' . json_encode($errorInfo));
            echo json_encode([
                'success' => false, 
                'message' => 'Database error occurred',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            error_log('Create doctor exception: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'An error occurred: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update() {
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
            if (empty($input['id'])) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>'ID is required']);
                return;
            }
            $id = (int)$input['id'];
            unset($input['id']);

            // Validate email format if email is being updated
            if (isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>'Invalid email format']);
                return;
            }

            $ok = $this->user->updateDoctor($id, $input);
            if ($ok) {
                echo json_encode(['success'=>true,'message'=>'Doctor updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success'=>false,'message'=>'Failed to update doctor']);
            }
        } catch (PDOException $e) {
            ob_clean();
            http_response_code(500);
            error_log('Update doctor PDO error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            error_log('Update doctor exception: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function delete() {
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
            if (empty($input['id'])) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>'ID is required']);
                return;
            }
            $id = (int)$input['id'];
            $ok = $this->user->deleteDoctor($id);
            if ($ok) {
                echo json_encode(['success'=>true,'message'=>'Doctor deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success'=>false,'message'=>'Failed to delete doctor']);
            }
        } catch (PDOException $e) {
            ob_clean();
            http_response_code(500);
            error_log('Delete doctor PDO error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            error_log('Delete doctor exception: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function get() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid id']); return; }
        $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, department, bio, created_at FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($doctor) echo json_encode(['success'=>true,'data'=>$doctor]);
        else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Doctor not found']); }
    }
}

