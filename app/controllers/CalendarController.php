<?php
require_once __DIR__ . '/../models/CalendarEvent.php';

class CalendarController {
    private $eventModel;

    public function __construct() {
        $this->eventModel = new CalendarEvent();
    }

    public function list() {
        header('Content-Type: application/json');
        ob_clean();
        try {
            $filters = [];
            if (!empty($_GET['event_type'])) $filters['event_type'] = $_GET['event_type'];
            if (!empty($_GET['department'])) $filters['department'] = $_GET['department'];
            if (!empty($_GET['start_date'])) $filters['start_date'] = $_GET['start_date'];
            if (!empty($_GET['end_date'])) $filters['end_date'] = $_GET['end_date'];
            if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];

            $events = $this->eventModel->listEvents($filters);
            echo json_encode(['success' => true, 'data' => $events]);
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
                echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
                return;
            }
            $event = $this->eventModel->getEventById($id);
            if ($event) {
                echo json_encode(['success' => true, 'data' => $event]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Event not found']);
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

        $required = ['title', 'start_date'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $f)) . ' is required']);
                return;
            }
        }

        try {
            $eventId = $this->eventModel->createEvent($input);
            if ($eventId) {
                echo json_encode(['success' => true, 'message' => 'Event created successfully', 'id' => $eventId]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create event']);
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
            echo json_encode(['success' => false, 'message' => 'Event ID is required']);
            return;
        }
        $id = (int)$input['id'];
        unset($input['id']);

        try {
            $ok = $this->eventModel->updateEvent($id, $input);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update event']);
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
            echo json_encode(['success' => false, 'message' => 'Event ID is required']);
            return;
        }
        $id = (int)$input['id'];

        try {
            $ok = $this->eventModel->deleteEvent($id);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
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
            $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
            $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
            $stats = $this->eventModel->getEventStats($month, $year);
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function upcoming() {
        header('Content-Type: application/json');
        ob_clean();
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $events = $this->eventModel->getUpcomingEvents($limit);
            echo json_encode(['success' => true, 'data' => $events]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}

