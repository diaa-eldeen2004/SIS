<?php
require_once __DIR__ . '/../models/User.php';

/**
 * Admin controller for creating users with different roles
 * Only admins should have access to these endpoints
 */
class AdminUserController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    /**
     * Create a student (admin only)
     */
    public function createStudent() {
        // Don't call ob_clean() or header() here - let the API file handle it
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            // Get input data
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            // If JSON decode failed, try POST data
            if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
                $input = $_POST;
            }
            
            // Validate input exists
            if (empty($input) || !is_array($input)) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>'Invalid input data', 'debug' => 'Input was empty or not an array']);
                exit;
            }

            // Basic validation
            $required = ['first_name','last_name','email'];
            foreach ($required as $f) {
                if (empty($input[$f])) {
                    http_response_code(400);
                    echo json_encode(['success'=>false,'message'=>"$f is required", 'field' => $f]);
                    exit;
                }
            }

            // Validate email format
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>'Invalid email format']);
                exit;
            }

            // Check if email already exists
            if ($this->user->emailExists($input['email'])) {
                http_response_code(409);
                echo json_encode(['success'=>false,'message'=>'Email already registered']);
                exit;
            }

            // Hash password (use provided or default)
            $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
            $input['password'] = $password;

            // Attempt to create student
            $ok = $this->user->createStudent($input);
            if ($ok) {
                echo json_encode(['success'=>true,'message'=>'Student created successfully']);
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['success'=>false,'message'=>'Failed to create student - method returned false']);
                exit;
            }
        } catch (PDOException $e) {
            ob_clean();
            http_response_code(500);
            $errorMsg = $e->getMessage();
            $errorInfo = $e->errorInfo ?? [];
            error_log('Create student PDO error: ' . $errorMsg);
            error_log('PDO Error Info: ' . json_encode($errorInfo));
            echo json_encode([
                'success' => false, 
                'message' => 'Database error occurred', 
                'error' => $errorMsg,
                'code' => $e->getCode(),
                'sqlstate' => $errorInfo[0] ?? 'N/A',
                'driver_code' => $errorInfo[1] ?? 'N/A',
                'driver_message' => $errorInfo[2] ?? $errorMsg,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            exit;
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            $errorMsg = $e->getMessage();
            error_log('Create student exception: ' . $errorMsg);
            echo json_encode([
                'success' => false, 
                'message' => 'An error occurred', 
                'error' => $errorMsg,
                'type' => get_class($e)
            ]);
            exit;
        } catch (Throwable $e) {
            ob_clean();
            http_response_code(500);
            $errorMsg = $e->getMessage();
            error_log('Create student throwable: ' . $errorMsg);
            echo json_encode([
                'success' => false, 
                'message' => 'An unexpected error occurred', 
                'error' => $errorMsg,
                'type' => get_class($e)
            ]);
            exit;
        }
    }

    /**
     * Create a doctor (admin only)
     */
    public function createDoctor() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;

        // Basic validation
        $required = ['first_name','last_name','email','department'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['success'=>false,'message'=>"$f is required"]);
                return;
            }
        }

        // Check if email already exists
        if ($this->user->emailExists($input['email'])) {
            http_response_code(409);
            echo json_encode(['success'=>false,'message'=>'Email already registered']);
            return;
        }

        // Hash password (use provided or default)
        $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
        $input['password'] = $password;

        $ok = $this->user->createDoctor($input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Doctor created']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create doctor']); }
    }

    /**
     * Create an IT officer (admin only)
     */
    public function createITOfficer() {
        header('Content-Type: application/json');
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

        // Check if email already exists
        if ($this->user->emailExists($input['email'])) {
            http_response_code(409);
            echo json_encode(['success'=>false,'message'=>'Email already registered']);
            return;
        }

        // Hash password (use provided or default)
        $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
        $input['password'] = $password;

        $ok = $this->user->createITOfficer($input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'IT Officer created']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create IT Officer']); }
    }

    /**
     * Create an advisor (admin only)
     */
    public function createAdvisor() {
        header('Content-Type: application/json');
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

        // Check if email already exists
        if ($this->user->emailExists($input['email'])) {
            http_response_code(409);
            echo json_encode(['success'=>false,'message'=>'Email already registered']);
            return;
        }

        // Hash password (use provided or default)
        $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
        $input['password'] = $password;

        $ok = $this->user->createAdvisor($input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Advisor created']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create advisor']); }
    }

    /**
     * Create an admin (admin only)
     */
    public function createAdmin() {
        header('Content-Type: application/json');
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

        // Check if email already exists
        if ($this->user->emailExists($input['email'])) {
            http_response_code(409);
            echo json_encode(['success'=>false,'message'=>'Email already registered']);
            return;
        }

        // Hash password (use provided or default)
        $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
        $input['password'] = $password;

        $ok = $this->user->createAdmin($input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'Admin created']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create admin']); }
    }

    /**
     * Create a regular user (admin only)
     */
    public function createUser() {
        header('Content-Type: application/json');
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

        // Check if email already exists
        if ($this->user->emailExists($input['email'])) {
            http_response_code(409);
            echo json_encode(['success'=>false,'message'=>'Email already registered']);
            return;
        }

        // Hash password (use provided or default)
        $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
        $input['password'] = $password;

        $ok = $this->user->create($input);
        if ($ok) echo json_encode(['success'=>true,'message'=>'User created']);
        else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create user']); }
    }
}

