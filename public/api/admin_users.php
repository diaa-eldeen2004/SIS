<?php
// Admin Users API - for creating users with different roles
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error occurred',
            'error' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => 'Fatal Error'
        ], JSON_PRETTY_PRINT);
        exit;
    }
});

// Start output buffering early
ob_start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    ob_clean(); 
    http_response_code(200); 
    exit; 
}

// TODO: Add admin authentication check here

try {
    // Clean any previous output
    ob_clean();
    
    // Load controller
    $controllerPath = __DIR__ . '/../../app/controllers/AdminUserController.php';
    if (!file_exists($controllerPath)) {
        throw new Exception("Controller file not found: $controllerPath");
    }
    require_once $controllerPath;
    
    // Check if class exists
    if (!class_exists('AdminUserController')) {
        throw new Exception("AdminUserController class not found after require");
    }
    
    $action = $_GET['action'] ?? '';
    $ctrl = new AdminUserController();

    switch ($action) {
        case 'create-student':
            $ctrl->createStudent();
            break;
        case 'create-doctor':
            $ctrl->createDoctor();
            break;
        case 'create-it':
            $ctrl->createITOfficer();
            break;
        case 'create-advisor':
            $ctrl->createAdvisor();
            break;
        case 'create-admin':
            $ctrl->createAdmin();
            break;
        case 'create-user':
            $ctrl->createUser();
            break;
        default:
            ob_clean();
            http_response_code(404);
            echo json_encode(['success'=>false,'message'=>'Route not found', 'action' => $action]);
            exit;
    }
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    $errorInfo = $e->errorInfo ?? [];
    $error = [
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'sqlstate' => $errorInfo[0] ?? 'N/A',
        'driver_code' => $errorInfo[1] ?? 'N/A',
        'driver_message' => $errorInfo[2] ?? $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    error_log('AdminUsers API PDO Error: ' . $e->getMessage() . ' | SQL State: ' . ($errorInfo[0] ?? 'N/A') . ' | Driver: ' . ($errorInfo[2] ?? 'N/A') . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode($error, JSON_PRETTY_PRINT);
    exit;
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    $error = [
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'type' => get_class($e)
    ];
    error_log('AdminUsers API Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode($error);
    exit;
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    $error = [
        'success' => false,
        'message' => 'An unexpected error occurred',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'type' => get_class($e)
    ];
    error_log('AdminUsers API Throwable: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode($error);
    exit;
}

