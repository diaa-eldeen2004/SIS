<?php
// Students API
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../../app/controllers/StudentController.php';
$action = $_GET['action'] ?? '';
$ctrl = new StudentController();
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
    case 'import':
        $ctrl->import();
        break;
    case 'bulk-update':
        $ctrl->bulkUpdate();
        break;
    case 'export':
        // export triggers a CSV download (not JSON)
        $ctrl->exportCsv();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Route not found']);
        break;
}
