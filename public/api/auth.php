<?php

// Set error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once __DIR__ . '/../../app/controllers/AuthController.php';

$action = $_GET['action'] ?? '';

$authController = new AuthController();

switch ($action) {
    case 'signup':
        $authController->signup();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Route not found']);
        break;
}

