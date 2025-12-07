<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Requests - IT Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Load enrollment requests server-side
require_once __DIR__ . '/../../core/Database.php';
$db = Database::getInstance()->getConnection();

// Initialize message variables
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['type']) ? $_GET['type'] : 'info';

// Handle form submissions
require_once __DIR__ . '/../../core/Logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve' && isset($_POST['enrollment_id'])) {
        try {
            $enrollmentId = (int)$_POST['enrollment_id'];
            
            // Get enrollment info for logging
            $infoStmt = $db->prepare("
                SELECT sc.*, CONCAT(st.first_name, ' ', st.last_name) as student_name, 
                       c.course_code, c.course_name
                FROM student_courses sc
                LEFT JOIN students st ON sc.student_id = st.id
                LEFT JOIN courses c ON sc.course_id = c.id
                WHERE sc.id = ?
            ");
            $infoStmt->execute([$enrollmentId]);
            $enrollmentInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if status column supports 'taking' or 'approved'
            $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($columns);
            
            if ($hasStatusColumn) {
                // Check what status values are allowed
                $statusInfo = $db->query("SHOW COLUMNS FROM student_courses WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
                $allowedValues = $statusInfo['Type'] ?? '';
                
                if (strpos($allowedValues, 'approved') !== false) {
                    $stmt = $db->prepare("UPDATE student_courses SET status = 'approved' WHERE id = ?");
                } else {
                    // Default to 'taking' if 'approved' is not available
                    $stmt = $db->prepare("UPDATE student_courses SET status = 'taking' WHERE id = ?");
                }
                $stmt->execute([$enrollmentId]);
            } else {
                // No status column, just update enrolled_at
                $stmt = $db->prepare("UPDATE student_courses SET enrolled_at = NOW() WHERE id = ?");
                $stmt->execute([$enrollmentId]);
            }
            
            // Log the approval
            Logger::success("Enrollment request approved", [
                'enrollment_id' => $enrollmentId,
                'student_id' => $enrollmentInfo['student_id'] ?? null,
                'student_name' => $enrollmentInfo['student_name'] ?? 'N/A',
                'course_id' => $enrollmentInfo['course_id'] ?? null,
                'course_code' => $enrollmentInfo['course_code'] ?? 'N/A',
                'course_name' => $enrollmentInfo['course_name'] ?? 'N/A'
            ], 'enrollment');
            
            header('Location: it_enrollments.php?message=' . urlencode('Enrollment approved successfully') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_enrollments.php?message=' . urlencode('Error approving enrollment: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'reject' && isset($_POST['enrollment_id'])) {
        try {
            $enrollmentId = (int)$_POST['enrollment_id'];
            $reason = $_POST['reason'] ?? null;
            
            // Get enrollment info for logging
            $infoStmt = $db->prepare("
                SELECT sc.*, CONCAT(st.first_name, ' ', st.last_name) as student_name, 
                       c.course_code, c.course_name
                FROM student_courses sc
                LEFT JOIN students st ON sc.student_id = st.id
                LEFT JOIN courses c ON sc.course_id = c.id
                WHERE sc.id = ?
            ");
            $infoStmt->execute([$enrollmentId]);
            $enrollmentInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if status column supports 'rejected'
            $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($columns);
            
            if ($hasStatusColumn) {
                $statusInfo = $db->query("SHOW COLUMNS FROM student_courses WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
                $allowedValues = $statusInfo['Type'] ?? '';
                
                if (strpos($allowedValues, 'rejected') !== false) {
                    $stmt = $db->prepare("UPDATE student_courses SET status = 'rejected' WHERE id = ?");
                    $stmt->execute([$enrollmentId]);
                } else {
                    // If rejected status not available, delete the enrollment request
                    $stmt = $db->prepare("DELETE FROM student_courses WHERE id = ?");
                    $stmt->execute([$enrollmentId]);
                }
            } else {
                // No status column, delete the enrollment
                $stmt = $db->prepare("DELETE FROM student_courses WHERE id = ?");
                $stmt->execute([$enrollmentId]);
            }
            
            // Log the rejection
            Logger::warning("Enrollment request rejected", [
                'enrollment_id' => $enrollmentId,
                'student_id' => $enrollmentInfo['student_id'] ?? null,
                'student_name' => $enrollmentInfo['student_name'] ?? 'N/A',
                'course_id' => $enrollmentInfo['course_id'] ?? null,
                'course_code' => $enrollmentInfo['course_code'] ?? 'N/A',
                'course_name' => $enrollmentInfo['course_name'] ?? 'N/A',
                'reason' => $reason
            ], 'enrollment');
            
            header('Location: it_enrollments.php?message=' . urlencode('Enrollment rejected' . ($reason ? ': ' . $reason : '')) . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_enrollments.php?message=' . urlencode('Error rejecting enrollment: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'bulk-approve' && isset($_POST['enrollment_ids'])) {
        try {
            $ids = is_array($_POST['enrollment_ids']) ? $_POST['enrollment_ids'] : explode(',', $_POST['enrollment_ids']);
            $ids = array_map('intval', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($columns);
            
            if ($hasStatusColumn) {
                $statusInfo = $db->query("SHOW COLUMNS FROM student_courses WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
                $allowedValues = $statusInfo['Type'] ?? '';
                
                if (strpos($allowedValues, 'approved') !== false) {
                    $stmt = $db->prepare("UPDATE student_courses SET status = 'approved' WHERE id IN ($placeholders)");
                } else {
                    $stmt = $db->prepare("UPDATE student_courses SET status = 'taking' WHERE id IN ($placeholders)");
                }
                $stmt->execute($ids);
            } else {
                $stmt = $db->prepare("UPDATE student_courses SET enrolled_at = NOW() WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            }
            
            header('Location: it_enrollments.php?message=' . urlencode(count($ids) . ' enrollment(s) approved successfully') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_enrollments.php?message=' . urlencode('Error approving enrollments: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'bulk-reject' && isset($_POST['enrollment_ids'])) {
        try {
            $ids = is_array($_POST['enrollment_ids']) ? $_POST['enrollment_ids'] : explode(',', $_POST['enrollment_ids']);
            $ids = array_map('intval', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($columns);
            
            if ($hasStatusColumn) {
                $statusInfo = $db->query("SHOW COLUMNS FROM student_courses WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
                $allowedValues = $statusInfo['Type'] ?? '';
                
                if (strpos($allowedValues, 'rejected') !== false) {
                    $stmt = $db->prepare("UPDATE student_courses SET status = 'rejected' WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                } else {
                    $stmt = $db->prepare("DELETE FROM student_courses WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                }
            } else {
                $stmt = $db->prepare("DELETE FROM student_courses WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            }
            
            header('Location: it_enrollments.php?message=' . urlencode(count($ids) . ' enrollment(s) rejected') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_enrollments.php?message=' . urlencode('Error rejecting enrollments: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    }
}

// Get filters
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$courseFilter = isset($_GET['course']) ? trim($_GET['course']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query for enrollment requests
$where = "WHERE 1=1";
$params = [];

// Check if status column exists
try {
    $colCheck = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
    $hasStatusColumn = !empty($colCheck);
} catch (Exception $e) {
    $hasStatusColumn = false;
}

// Apply status filter if provided
if ($hasStatusColumn && $statusFilter !== '') {
    if ($statusFilter === 'pending') {
        // Show pending or null status
        $where .= " AND (sc.status = 'pending' OR sc.status IS NULL)";
    } else {
        $where .= " AND sc.status = ?";
        $params[] = $statusFilter;
    }
}

if ($courseFilter !== '') {
    $where .= " AND c.course_code = ?";
    $params[] = $courseFilter;
}

if ($search !== '') {
    $where .= " AND (st.first_name LIKE ? OR st.last_name LIKE ? OR st.student_number LIKE ? OR c.course_code LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Fetch enrollment requests
$enrollments = [];
$stats = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'total' => 0
];

try {
    // Get stats
    if ($hasStatusColumn) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM student_courses WHERE status = 'pending' OR status IS NULL");
            $stats['pending'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            // Ignore
        }
        
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM student_courses WHERE status = 'approved'");
            $stats['approved'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            // Ignore
        }
        
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM student_courses WHERE status = 'rejected'");
            $stats['rejected'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            // Ignore
        }
    }
    
    $stmt = $db->query("SELECT COUNT(*) FROM student_courses");
    $stats['total'] = (int)$stmt->fetchColumn();
    
    // Get enrollments
    $selectFields = "sc.id, sc.student_id, sc.course_id, sc.status";
    $joinSections = "";
    $orderBy = "sc.enrolled_at DESC";
    
    // Check for section_id and requested_at columns
    $columns = $db->query("SHOW COLUMNS FROM student_courses")->fetchAll(PDO::FETCH_COLUMN);
    $hasSectionId = in_array('section_id', $columns);
    $hasRequestedAt = in_array('requested_at', $columns);
    
    if ($hasSectionId) {
        $selectFields .= ", sc.section_id";
        $joinSections = "LEFT JOIN sections s ON sc.section_id = s.id";
        $selectFields .= ", s.section_code";
    }
    
    if ($hasRequestedAt) {
        $selectFields .= ", sc.requested_at";
        $orderBy = "sc.requested_at DESC";
    } else {
        $selectFields .= ", sc.enrolled_at as requested_at";
    }
    
    $stmt = $db->prepare("
        SELECT $selectFields,
               CONCAT(st.first_name, ' ', st.last_name) as student_name,
               st.student_number,
               st.email as student_email,
               c.course_code,
               c.course_name
        FROM student_courses sc
        LEFT JOIN students st ON sc.student_id = st.id
        LEFT JOIN courses c ON sc.course_id = c.id
        $joinSections
        $where
        ORDER BY $orderBy
        LIMIT 100
    ");
    $stmt->execute($params);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error loading enrollments: " . $e->getMessage());
}

// Get unique courses for filter
$courses = [];
try {
    $courseStmt = $db->query("SELECT DISTINCT course_code FROM courses WHERE course_code IS NOT NULL AND course_code != '' ORDER BY course_code");
    $courses = $courseStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Ignore
}
?>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" title="Switch to dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-laptop-code"></i> IT Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="it_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="it_schedule.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Semester Schedule
            </a>
            <a href="it_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Course Management
            </a>
            <a href="it_enrollments.php" class="nav-item active">
                <i class="fas fa-user-check"></i> Enrollment Requests
            </a>
            <a href="it_logs.php" class="nav-item">
                <i class="fas fa-file-alt"></i> System Logs
            </a>
            <a href="../settings.php" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../auth/logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Content Header -->
        <header class="content-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin: 0; color: var(--text-primary);">Enrollment Requests</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Approve or reject student enrollment requests.</p>
                </div>
                <a href="it_enrollments.php" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Refresh
                </a>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Stats -->
            <section class="enrollment-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['pending']); ?></div>
                        <div style="color: var(--text-secondary);">Pending Requests</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['approved']); ?></div>
                        <div style="color: var(--text-secondary);">Approved</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['rejected']); ?></div>
                        <div style="color: var(--text-secondary);">Rejected</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['total']); ?></div>
                        <div style="color: var(--text-secondary);">Total Requests</div>
                    </div>
                </div>
            </section>

            <!-- Filters -->
            <section class="enrollment-filters" style="margin-bottom: 2rem;">
                <div class="card">
                    <form method="GET" action="it_enrollments.php" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-input" placeholder="Search by student name, course..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="taking" <?php echo $statusFilter === 'taking' ? 'selected' : ''; ?>>Taking</option>
                                <option value="taken" <?php echo $statusFilter === 'taken' ? 'selected' : ''; ?>>Taken</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Course</label>
                            <select name="course" class="form-input" onchange="this.form.submit()">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $courseFilter === $course ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="it_enrollments.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Enrollment Requests List -->
            <section class="enrollment-requests">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-list" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Enrollment Requests
                        </h2>
                    </div>
                    <div>
                        <?php if (empty($enrollments)): ?>
                            <div style="padding: 3rem; text-align: center;">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary);">No enrollment requests found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <?php
                                $statusColor = 'var(--secondary-color)';
                                $statusText = ucfirst($enrollment['status'] ?? 'pending');
                                if (($enrollment['status'] ?? 'pending') === 'pending' || empty($enrollment['status'])) {
                                    $statusColor = 'var(--warning-color)';
                                    $statusText = 'Pending';
                                } elseif ($enrollment['status'] === 'approved' || $enrollment['status'] === 'taking') {
                                    $statusColor = 'var(--success-color)';
                                    $statusText = 'Approved/Taking';
                                } elseif ($enrollment['status'] === 'rejected') {
                                    $statusColor = 'var(--error-color)';
                                    $statusText = 'Rejected';
                                }
                                $isPending = (empty($enrollment['status']) || $enrollment['status'] === 'pending');
                                ?>
                                <div style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border-bottom: 1px solid var(--border-color); <?php echo $isPending ? 'background-color: rgba(245, 158, 11, 0.05);' : ''; ?>">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                            <h4 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($enrollment['student_name'] ?? 'Unknown Student'); ?></h4>
                                            <span class="badge" style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </div>
                                        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                                            <div>
                                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Course:</span>
                                                <strong style="color: var(--text-primary); margin-left: 0.5rem;"><?php echo htmlspecialchars($enrollment['course_code'] ?? 'N/A'); ?></strong>
                                                <?php if (!empty($enrollment['course_name'])): ?>
                                                    <span style="color: var(--text-secondary); font-size: 0.9rem;"> - <?php echo htmlspecialchars($enrollment['course_name']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($enrollment['section_code'])): ?>
                                                <div>
                                                    <span style="color: var(--text-secondary); font-size: 0.9rem;">Section:</span>
                                                    <strong style="color: var(--text-primary); margin-left: 0.5rem;"><?php echo htmlspecialchars($enrollment['section_code']); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Student ID:</span>
                                                <strong style="color: var(--text-primary); margin-left: 0.5rem;"><?php echo htmlspecialchars($enrollment['student_number'] ?? 'N/A'); ?></strong>
                                            </div>
                                            <div>
                                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Requested:</span>
                                                <strong style="color: var(--text-primary); margin-left: 0.5rem;">
                                                    <?php 
                                                    $dateStr = $enrollment['requested_at'] ?? '';
                                                    if ($dateStr) {
                                                        echo date('M j, Y g:i A', strtotime($dateStr));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($isPending): ?>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this enrollment?');">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                                <button type="submit" class="btn btn-success" style="padding: 0.5rem 1rem;">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <button class="btn btn-error" onclick="showRejectModal(<?php echo $enrollment['id']; ?>)" style="padding: 0.5rem 1rem;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Portal</h3>
                <p>Comprehensive campus management system designed to streamline academic operations and enhance the learning experience.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="../home.php">Home</a>
                <a href="../about.php">About Us</a>
                <a href="../contact.php">Contact</a>
                <a href="../help_center.php">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.php">Student Login</a>
                <a href="../auth/auth_login.php">Doctor Login</a>
                <a href="../auth/auth_login.php">Admin Login</a>
                <a href="../auth/auth_sign.php">Register</a>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-envelope"></i> info@university.edu</p>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                <p><i class="fas fa-map-marker-alt"></i> 123 University Ave, Campus City</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 University Portal. All rights reserved. | Privacy Policy | Terms of Service</p>
        </div>
    </footer>

    <!-- Reject Enrollment Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Reject Enrollment Request</h2>
                <button class="modal-close" onclick="closeRejectModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="rejectForm" method="POST" action="it_enrollments.php">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" id="rejectEnrollmentId" name="enrollment_id">
                    <div class="form-group">
                        <label class="form-label">Reason for Rejection (Optional)</label>
                        <textarea name="reason" class="form-input" rows="3" placeholder="Provide a reason for rejection..."></textarea>
                    </div>
                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Cancel</button>
                        <button type="submit" class="btn btn-error">Reject Enrollment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../js/main.js"></script>
    <script>
        // Show toast notification on page load if there's a message
        <?php if (!empty($message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const messageType = '<?php echo htmlspecialchars($messageType, ENT_QUOTES); ?>';
            const message = <?php echo json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            
            let backgroundColor = '#2563eb'; // default blue
            if (messageType === 'success') {
                backgroundColor = '#10b981'; // green
            } else if (messageType === 'error') {
                backgroundColor = '#ef4444'; // red
            } else if (messageType === 'warning') {
                backgroundColor = '#f59e0b'; // orange
            }
            
            Toastify({
                text: message,
                duration: 5000,
                gravity: "top",
                position: "right",
                style: {
                    background: backgroundColor,
                },
                close: true,
            }).showToast();
            
            // Clean URL by removing message parameters
            if (window.location.search.includes('message=')) {
                const url = new URL(window.location);
                url.searchParams.delete('message');
                url.searchParams.delete('type');
                window.history.replaceState({}, '', url);
            }
        });
        <?php endif; ?>

        function showRejectModal(enrollmentId) {
            document.getElementById('rejectEnrollmentId').value = enrollmentId;
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('active');
            document.getElementById('rejectForm').reset();
        }

        // Close modals on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
