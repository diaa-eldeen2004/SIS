<?php
require_once __DIR__ . '/../core/Database.php';

class Report {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listReports($filters = []) {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (title LIKE ? OR description LIKE ?)";
            $like = "%" . $filters['search'] . "%";
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($filters['type'])) {
            $where .= " AND type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['period'])) {
            $where .= " AND period = ?";
            $params[] = $filters['period'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $stmt = $this->db->prepare("SELECT * FROM reports $where ORDER BY created_at DESC LIMIT 100");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportById($id) {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createReport($data) {
        $stmt = $this->db->prepare("
            INSERT INTO reports (title, description, type, period, status, generated_by, scheduled_at, parameters, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $success = $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['type'] ?? 'academic',
            $data['period'] ?? 'monthly',
            $data['status'] ?? 'generating',
            $data['generated_by'] ?? null,
            $data['scheduled_at'] ?? null,
            $data['parameters'] ?? null
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    public function updateReport($id, $data) {
        $set = [];
        $params = [];
        $allowed = ['title', 'description', 'type', 'period', 'status', 'file_path', 'file_format', 'scheduled_at', 'generated_at', 'parameters', 'error_message'];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $set[] = "`$key` = ?";
                $params[] = $data[$key];
            }
        }
        if (empty($set)) return false;
        $set[] = "`updated_at` = NOW()";
        $params[] = $id;

        $stmt = $this->db->prepare("UPDATE reports SET " . implode(', ', $set) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function deleteReport($id) {
        $stmt = $this->db->prepare("DELETE FROM reports WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function incrementDownloadCount($id) {
        $stmt = $this->db->prepare("UPDATE reports SET download_count = download_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getReportStats() {
        $stats = [];
        
        // Total reports
        $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM reports");
        $stats['total'] = (int)$stmt->fetchColumn();
        
        // Reports generated today
        $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM reports WHERE DATE(created_at) = CURDATE()");
        $stats['today'] = (int)$stmt->fetchColumn();
        
        // Scheduled reports
        $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM reports WHERE status = 'scheduled'");
        $stats['scheduled'] = (int)$stmt->fetchColumn();
        
        // Total downloads
        $stmt = $this->db->query("SELECT SUM(download_count) as cnt FROM reports");
        $stats['downloads'] = (int)$stmt->fetchColumn();
        
        return $stats;
    }
}

