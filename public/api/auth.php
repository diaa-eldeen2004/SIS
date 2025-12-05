<?php

// Set error reporting (disable in production)
error_reporting(E_ALL);
// Log errors but don't display them (to avoid breaking JSON)
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header FIRST before any output
header('Content-Type: application/json');

// Start output buffering to catch any errors
ob_start();

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
try {
    require_once __DIR__ . '/../../app/controllers/AuthController.php';
} catch (Throwable $e) {
    ob_clean(); // Clear any output
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load controller',
        'error' => $e->getMessage()
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    $authController = new AuthController();
} catch (Throwable $e) {
    ob_clean(); // Clear any output
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to initialize controller',
        'error' => $e->getMessage()
    ]);
    exit;
}

try {
    switch ($action) {
        case 'signup':
            $authController->signup();
            break;
        case 'login':
            $authController->login();
            break;
        case 'request-password-reset':
            $authController->requestPasswordReset();
            break;
        case 'reset-password':
            $authController->resetPassword();
            break;
        default:
            ob_clean(); // Clear any output
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Route not found']);
            break;
    }
} catch (Throwable $e) {
    ob_clean(); // Clear any output
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}

