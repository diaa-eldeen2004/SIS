<?php
require_once __DIR__ . '/../models/User.php';

class ITOfficerController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function list() {
        ob_clean();
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        
        $search = $_GET['search'] ?? '';
        $department = $_GET['department'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        
        if ($department) {
            $where .= " AND department = ?";
            $params[] = $department;
        }
        
        try {
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, department, specialization, office_location, created_at FROM it_officers $where ORDER BY created_at DESC LIMIT 100");
            $stmt->execute($params);
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

            $required = ['first_name','last_name','email'];
            foreach ($required as $f) {
                if (empty($input[$f])) {
                    http_response_code(400);
                    echo json_encode(['success'=>false,'message'=>"$f is required"]);
                    return;
                }
            }

            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                return;
            }

            if ($this->user->emailExists($input['email'])) {
                http_response_code(409);
                echo json_encode(['success'=>false,'message'=>'Email already exists']);
                return;
            }

            $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
            $input['password'] = $password;

            // createITOfficer throws exceptions on error, so we don't need to check return value
            $this->user->createITOfficer($input);
            echo json_encode(['success'=>true,'message'=>'IT Officer created successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("ITOfficerController create PDO error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("ITOfficerController create general error: " . $e->getMessage());
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
        $ok = $this->user->updateITOfficer($id, $input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'IT Officer updated']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to update IT Officer']); }
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
        $ok = $this->user->deleteITOfficer($id);
        if ($ok) echo json_encode(['success'=>true,'message'=>'IT Officer deleted']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to delete IT Officer']); }
    }

    public function get() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid id']); return; }
        $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, department, specialization, office_location, created_at FROM it_officers WHERE id = ?");
        $stmt->execute([$id]);
        $it = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($it) echo json_encode(['success'=>true,'data'=>$it]);
        else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'IT Officer not found']); }
    }
}

