<?php
require_once __DIR__ . '/../models/Report.php';

class ReportController {
    private $reportModel;

    public function __construct() {
        $this->reportModel = new Report();
    }

    public function list() {
        header('Content-Type: application/json');
        ob_clean();
        try {
            $filters = [];
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
            if (!empty($_GET['type'])) $filters['type'] = $_GET['type'];
            if (!empty($_GET['period'])) $filters['period'] = $_GET['period'];
            if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];

            $reports = $this->reportModel->listReports($filters);
            echo json_encode(['success' => true, 'data' => $reports]);
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
        ob_clean();
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
                return;
            }
            $report = $this->reportModel->getReportById($id);
            if ($report) {
                echo json_encode(['success' => true, 'data' => $report]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Report not found']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function create() {
        header('Content-Type: application/json');
        ob_clean();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;

        $required = ['title'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $f)) . ' is required']);
                return;
            }
        }

        try {
            $reportId = $this->reportModel->createReport($input);
            if ($reportId) {
                echo json_encode(['success' => true, 'message' => 'Report created successfully', 'id' => $reportId]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create report']);
            }
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
        ob_clean();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;

        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Report ID is required']);
            return;
        }
        $id = (int)$input['id'];
        unset($input['id']);

        try {
            $ok = $this->reportModel->updateReport($id, $input);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Report updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update report']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function delete() {
        header('Content-Type: application/json');
        ob_clean();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;

        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Report ID is required']);
            return;
        }
        $id = (int)$input['id'];

        try {
            $ok = $this->reportModel->deleteReport($id);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete report']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function stats() {
        header('Content-Type: application/json');
        ob_clean();
        try {
            $stats = $this->reportModel->getReportStats();
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function download() {
        header('Content-Type: application/json');
        ob_clean();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;

        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Report ID is required']);
            return;
        }
        $id = (int)$input['id'];

        try {
            $this->reportModel->incrementDownloadCount($id);
            $report = $this->reportModel->getReportById($id);
            if ($report && $report['file_path']) {
                echo json_encode(['success' => true, 'message' => 'Download initiated', 'file_path' => $report['file_path']]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Report file not found']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}

