<?php
require_once __DIR__ . '/../models/User.php';

class AdminController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function list() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, admin_level, permissions, created_at FROM admins ORDER BY created_at DESC LIMIT 100");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
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

        $ok = $this->user->createAdmin($input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Admin created']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create admin']); }
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
        $ok = $this->user->updateAdmin($id, $input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Admin updated']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to update admin']); }
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
        $ok = $this->user->deleteAdmin($id);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Admin deleted']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to delete admin']); }
    }

    public function get() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid id']); return; }
        $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, admin_level, permissions, created_at FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) echo json_encode(['success'=>true,'data'=>$admin]);
        else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Admin not found']); }
    }
}

