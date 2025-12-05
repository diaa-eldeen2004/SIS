<?php
// Reports API
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

ob_start();
try {
    require_once __DIR__ . '/../../app/controllers/ReportController.php';
    $action = $_GET['action'] ?? '';
    $ctrl = new ReportController();
    
    switch ($action) {
        case 'list':
            $ctrl->list();
            break;
        case 'get':
            $ctrl->get();
            break;
        case 'create':
            $ctrl->create();
            break;
        case 'update':
            $ctrl->update();
            break;
        case 'delete':
            $ctrl->delete();
            break;
        case 'stats':
            $ctrl->stats();
            break;
        case 'download':
            $ctrl->download();
            break;
        default:
            http_response_code(404);
            echo json_encode(['success'=>false,'message'=>'Route not found']);
            break;
    }
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

