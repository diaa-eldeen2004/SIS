<?php
require_once __DIR__ . '/../core/Database.php';

class CalendarEvent {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listEvents($filters = []) {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['event_type'])) {
            $where .= " AND event_type = ?";
            $params[] = $filters['event_type'];
        }
        if (!empty($filters['department'])) {
            $where .= " AND department = ?";
            $params[] = $filters['department'];
        }
        if (!empty($filters['start_date'])) {
            $where .= " AND start_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where .= " AND start_date <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $stmt = $this->db->prepare("SELECT * FROM calendar_events $where ORDER BY start_date ASC, start_time ASC LIMIT 200");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id) {
        $stmt = $this->db->prepare("SELECT * FROM calendar_events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEvent($data) {
        $stmt = $this->db->prepare("
            INSERT INTO calendar_events (
                title, description, event_type, start_date, start_time, end_date, end_time,
                all_day, location, department, course_id, course_code, created_by,
                affected_users, recurring, recurrence_pattern, recurrence_end_date, status,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $success = $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['event_type'] ?? 'event',
            $data['start_date'],
            $data['start_time'] ?? null,
            $data['end_date'] ?? null,
            $data['end_time'] ?? null,
            $data['all_day'] ?? false,
            $data['location'] ?? null,
            $data['department'] ?? null,
            $data['course_id'] ?? null,
            $data['course_code'] ?? null,
            $data['created_by'] ?? null,
            $data['affected_users'] ?? null,
            $data['recurring'] ?? false,
            $data['recurrence_pattern'] ?? null,
            $data['recurrence_end_date'] ?? null,
            $data['status'] ?? 'active'
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    public function updateEvent($id, $data) {
        $set = [];
        $params = [];
        $allowed = ['title', 'description', 'event_type', 'start_date', 'start_time', 'end_date', 'end_time', 
                   'all_day', 'location', 'department', 'course_id', 'course_code', 'affected_users', 
                   'recurring', 'recurrence_pattern', 'recurrence_end_date', 'status'];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $set[] = "`$key` = ?";
                $params[] = $data[$key];
            }
        }
        if (empty($set)) return false;
        $set[] = "`updated_at` = NOW()";
        $params[] = $id;

        $stmt = $this->db->prepare("UPDATE calendar_events SET " . implode(', ', $set) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function deleteEvent($id) {
        $stmt = $this->db->prepare("DELETE FROM calendar_events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getEventStats($month = null, $year = null) {
        $stats = [];
        
        if ($month && $year) {
            $where = "WHERE YEAR(start_date) = ? AND MONTH(start_date) = ?";
            $params = [$year, $month];
        } else {
            $where = "WHERE 1=1";
            $params = [];
        }
        
        // Total events this month
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM calendar_events $where");
        $stmt->execute($params);
        $stats['total'] = (int)$stmt->fetchColumn();
        
        // Exams scheduled
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM calendar_events $where AND event_type = 'exam'");
        $stmt->execute($params);
        $stats['exams'] = (int)$stmt->fetchColumn();
        
        // Conflicts (events on same date/time - simplified check)
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT e1.id) as cnt 
            FROM calendar_events e1
            INNER JOIN calendar_events e2 ON e1.start_date = e2.start_date 
                AND e1.start_time = e2.start_time 
                AND e1.id != e2.id
                AND e1.status = 'active' 
                AND e2.status = 'active'
            $where
        ");
        if ($month && $year) {
            $stmt->execute([$year, $month]);
        } else {
            $stmt->execute();
        }
        $stats['conflicts'] = (int)$stmt->fetchColumn();
        
        // People affected (simplified - count active events)
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM calendar_events $where AND status = 'active'");
        $stmt->execute($params);
        $stats['affected'] = (int)$stmt->fetchColumn() * 10; // Rough estimate
        
        return $stats;
    }

    public function getUpcomingEvents($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM calendar_events 
            WHERE start_date >= CURDATE() AND status = 'active'
            ORDER BY start_date ASC, start_time ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

