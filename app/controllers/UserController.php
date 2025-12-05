<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
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
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        
        try {
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, created_at FROM users $where ORDER BY created_at DESC LIMIT 100");
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
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) echo json_encode(['success'=>true,'data'=>$user]);
            else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'User not found']); }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
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

            if ($this->user->emailExists($input['email'])) {
                http_response_code(409);
                echo json_encode(['success'=>false,'message'=>'Email already exists']);
                return;
            }

            $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
            $input['password'] = $password;

            $ok = $this->user->createUser($input);
            if ($ok) echo json_encode(['success'=>true,'message'=>'User created']);
            else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create user']); }
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
        if (empty($input['id'])) { 
            http_response_code(400); 
            echo json_encode(['success'=>false,'message'=>'id is required']); 
            return; 
        }
        $id = (int)$input['id'];
        unset($input['id']);
        $ok = $this->user->updateUser($id, $input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'User updated']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to update user']); }
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
        $ok = $this->user->deleteUser($id);
        if ($ok) echo json_encode(['success'=>true,'message'=>'User deleted']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to delete user']); }
    }
}

