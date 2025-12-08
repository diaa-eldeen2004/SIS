<?php

/**
 * Logger Utility Class
 * Provides centralized logging functionality for the system
 */
class Logger {
    private static $db = null;
    
    /**
     * Get database connection
     */
    private static function getDB() {
        if (self::$db === null) {
            require_once __DIR__ . '/Database.php';
            $dbInstance = Database::getInstance();
            self::$db = $dbInstance->getConnection();
        }
        return self::$db;
    }
    
    /**
     * Log an error message
     */
    public static function error($message, $details = null, $source = 'system') {
        self::log('error', $message, $details, $source);
    }
    
    /**
     * Log a warning message
     */
    public static function warning($message, $details = null, $source = 'system') {
        self::log('warning', $message, $details, $source);
    }
    
    /**
     * Log an info message
     */
    public static function info($message, $details = null, $source = 'system') {
        self::log('info', $message, $details, $source);
    }
    
    /**
     * Log a success message
     */
    public static function success($message, $details = null, $source = 'system') {
        self::log('success', $message, $details, $source);
    }
    
    /**
     * Log a critical message
     */
    public static function critical($message, $details = null, $source = 'system') {
        self::log('critical', $message, $details, $source);
    }
    
    /**
     * Main logging method
     */
    private static function log($level, $message, $details = null, $source = 'system') {
        try {
            $db = self::getDB();
            
            // Get user information from session if available
            $userId = null;
            $userRole = null;
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }
            
            if (isset($_SESSION['role'])) {
                $userRole = $_SESSION['role'];
            }
            
            // Get IP address and user agent
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Serialize details if it's an array
            if (is_array($details) || is_object($details)) {
                $details = json_encode($details, JSON_PRETTY_PRINT);
            }
            
            $stmt = $db->prepare("
                INSERT INTO system_logs (level, source, message, details, user_id, user_role, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $level,
                $source,
                $message,
                $details,
                $userId,
                $userRole,
                $ipAddress,
                $userAgent
            ]);
        } catch (Exception $e) {
            // If logging fails, log to error_log as fallback
            error_log("Logger error: " . $e->getMessage());
            error_log("Failed to log: [$level] $source - $message");
        }
    }
    
    /**
     * Get logs with optional filters
     */
    public static function getLogs($filters = []) {
        try {
            $db = self::getDB();
            
            $where = ["1=1"];
            $params = [];
            
            // Level filter
            if (!empty($filters['level'])) {
                $where[] = "level = ?";
                $params[] = $filters['level'];
            }
            
            // Source filter
            if (!empty($filters['source'])) {
                $where[] = "source = ?";
                $params[] = $filters['source'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $where[] = "(message LIKE ? OR details LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
            }
            
            // Date range filter
            if (!empty($filters['dateRange'])) {
                switch ($filters['dateRange']) {
                    case 'today':
                        $where[] = "DATE(created_at) = CURDATE()";
                        break;
                    case 'week':
                        $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                        break;
                    case 'month':
                        $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                        break;
                    // 'all' doesn't add any date restriction
                }
            }
            
            $sql = "SELECT * FROM system_logs WHERE " . implode(" AND ", $where) . " ORDER BY created_at DESC LIMIT 1000";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get log statistics
     */
    public static function getStats($dateRange = 'month') {
        try {
            $db = self::getDB();
            
            $dateCondition = "";
            $params = [];
            
            if ($dateRange === 'today') {
                $dateCondition = "AND DATE(created_at) = CURDATE()";
            } elseif ($dateRange === 'week') {
                $dateCondition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($dateRange === 'month') {
                $dateCondition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            }
            
            $stats = [];
            
            // Total logs
            $stmt = $db->query("SELECT COUNT(*) FROM system_logs WHERE 1=1 $dateCondition");
            $stats['total'] = (int)$stmt->fetchColumn();
            
            // Error count
            $stmt = $db->query("SELECT COUNT(*) FROM system_logs WHERE level = 'error' $dateCondition");
            $stats['errors'] = (int)$stmt->fetchColumn();
            
            // Warning count
            $stmt = $db->query("SELECT COUNT(*) FROM system_logs WHERE level = 'warning' $dateCondition");
            $stats['warnings'] = (int)$stmt->fetchColumn();
            
            // Info count
            $stmt = $db->query("SELECT COUNT(*) FROM system_logs WHERE level = 'info' $dateCondition");
            $stats['info'] = (int)$stmt->fetchColumn();
            
            // Success count
            $stmt = $db->query("SELECT COUNT(*) FROM system_logs WHERE level = 'success' $dateCondition");
            $stats['success'] = (int)$stmt->fetchColumn();
            
            // Critical count
            $stmt = $db->query("SELECT COUNT(*) FROM system_logs WHERE level = 'critical' $dateCondition");
            $stats['critical'] = (int)$stmt->fetchColumn();
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting log stats: " . $e->getMessage());
            return [
                'total' => 0,
                'errors' => 0,
                'warnings' => 0,
                'info' => 0,
                'success' => 0,
                'critical' => 0
            ];
        }
    }
    
    /**
     * Clear logs (with optional filters)
     */
    public static function clearLogs($olderThan = null) {
        try {
            $db = self::getDB();
            
            if ($olderThan) {
                $stmt = $db->prepare("DELETE FROM system_logs WHERE created_at < ?");
                $stmt->execute([$olderThan]);
            } else {
                $db->exec("TRUNCATE TABLE system_logs");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error clearing logs: " . $e->getMessage());
            return false;
        }
    }
}

