<?php

require_once __DIR__ . '/../core/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * List students with optional filters: search (name/email), year, major, status
     */
    public function listStudents($filters = []) {
        $sql = "SELECT id, student_number, first_name, last_name, email, phone, year_enrolled, major, minor, gpa, status, created_at FROM students WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_number LIKE ? )";
            $like = "%" . $filters['search'] . "%";
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (!empty($filters['year'])) {
            $sql .= " AND year_enrolled = ?";
            $params[] = $filters['year'];
        }
        if (!empty($filters['major'])) {
            $sql .= " AND major = ?";
            $params[] = $filters['major'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Update a student by id
     */
    public function updateStudent($id, $data) {
        $fields = [];
        $params = [];
        $allowed = ['student_number','first_name','last_name','email','phone','year_enrolled','gpa','major','minor','transcript','status'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE students SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a student by id (cascades to users table via foreign key)
     */
    public function deleteStudent($id) {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Update an admin by id
     */
    public function updateAdmin($id, $data) {
        $fields = [];
        $params = [];
        $allowed = ['first_name','last_name','email','phone','admin_level','permissions'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE admins SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete an admin by id (cascades to users table via foreign key)
     */
    public function deleteAdmin($id) {
        $stmt = $this->db->prepare("DELETE FROM admins WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Bulk insert students from array of associative rows. Each row must include first_name,last_name,email,password (plain) optionally student_number,gpa,major,minor,status
     */
    public function bulkInsertStudents(array $rows) {
        $this->db->beginTransaction();
        try {
            foreach ($rows as $r) {
                $pwd = isset($r['password']) ? password_hash($r['password'], PASSWORD_DEFAULT) : password_hash('changeme', PASSWORD_DEFAULT);
                $this->createStudent([
                    'first_name' => $r['first_name'] ?? null,
                    'last_name' => $r['last_name'] ?? null,
                    'email' => $r['email'] ?? null,
                    'phone' => $r['phone'] ?? null,
                    'password' => $pwd,
                    'student_number' => $r['student_number'] ?? null,
                    'year_enrolled' => $r['year_enrolled'] ?? null,
                    'gpa' => $r['gpa'] ?? null,
                    'major' => $r['major'] ?? null,
                    'minor' => $r['minor'] ?? null,
                    'status' => $r['status'] ?? 'active'
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Bulk insert error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Export students according to filters — returns an array of rows
     */
    public function exportStudents($filters = []) {
        return $this->listStudents($filters);
    }

    /**
     * Check if email already exists (checks all tables)
     */
    public function emailExists($email) {
        // Check users table
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch() !== false) return true;
        
        // Check students table
        $stmt = $this->db->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch() !== false) return true;
        
        // Check doctors table
        $stmt = $this->db->prepare("SELECT id FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch() !== false) return true;
        
        // Check it_officers table
        $stmt = $this->db->prepare("SELECT id FROM it_officers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch() !== false) return true;
        
        // Check advisors table
        $stmt = $this->db->prepare("SELECT id FROM advisors WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch() !== false) return true;
        
        // Check admins table
        $stmt = $this->db->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch() !== false) return true;
        
        return false;
    }

    /**
     * Create a new user (default role only) - alias for create()
     */
    public function createUser($data) {
        try {
            // Check if phone column exists
            $checkPhone = $this->db->query("SHOW COLUMNS FROM users LIKE 'phone'");
            $hasPhone = $checkPhone->rowCount() > 0;
            
            if ($hasPhone) {
                $stmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, 'user', NOW(), NOW())
                ");
                $stmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, 'user', NOW(), NOW())
                ");
                $stmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['password'] ?? null
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log('Create user error: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            error_log('Create user error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new user (default role only)
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (first_name, last_name, email, phone, password, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 'user', NOW(), NOW())
        ");
        
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['password'],
        ]);
    }

    /**
     * Update a user by id
     */
    public function updateUser($id, $data) {
        $fields = [];
        $params = [];
        $allowed = ['first_name', 'last_name', 'email', 'phone'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a user by id
     */
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Create a student (admin action) - creates entry in both users and students tables
     */
    public function createStudent($data) {
        $this->db->beginTransaction();
        try {
            // Check if phone column exists in users table
            try {
                $checkPhone = $this->db->query("SHOW COLUMNS FROM users LIKE 'phone'");
                $hasPhone = $checkPhone->rowCount() > 0;
            } catch (PDOException $e) {
                $hasPhone = false;
            }

            // First create user entry - conditionally include phone if column exists
            // Note: role is set to 'user' for all entries in users table (students go in students table)
            $role = 'user';
            if ($hasPhone) {
                $userStmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $userStmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null,
                    $role
                ]);
            } else {
                $userStmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $userStmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['password'] ?? null,
                    $role
                ]);
            }
            $userId = $this->db->lastInsertId();
            
            // Validate that we got a valid user ID
            if (!$userId || $userId == 0) {
                throw new Exception("Failed to get user ID after insert. User creation may have failed.");
            }

            // Check if students table exists
            try {
                $checkTable = $this->db->query("SHOW TABLES LIKE 'students'");
                $studentsTableExists = $checkTable->rowCount() > 0;
            } catch (PDOException $e) {
                $studentsTableExists = false;
            }

            if (!$studentsTableExists) {
                // If students table doesn't exist, just create the user and return
                // This allows the system to work even if migrations haven't been run
                $this->db->commit();
                return true;
            }

            // Check which columns exist in students table
            try {
                $columnsStmt = $this->db->query("SHOW COLUMNS FROM students");
                $existingColumns = [];
                while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $existingColumns[] = $row['Field'];
                }
            } catch (PDOException $e) {
                // If we can't query columns, rollback and throw
                $this->db->rollBack();
                throw new Exception("Cannot access students table: " . $e->getMessage());
            }

            $hasUserId = in_array('user_id', $existingColumns);
            $hasPhoneStudent = in_array('phone', $existingColumns);
            $hasYearEnrolled = in_array('year_enrolled', $existingColumns);
            $hasStudentNumber = in_array('student_number', $existingColumns);
            $hasMajor = in_array('major', $existingColumns);
            $hasMinor = in_array('minor', $existingColumns);
            $hasGpa = in_array('gpa', $existingColumns);
            $hasTranscript = in_array('transcript', $existingColumns);
            $hasStatus = in_array('status', $existingColumns);

            // Ensure required NOT NULL fields are not null
            $firstName = $data['first_name'] ?? '';
            $lastName = $data['last_name'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $status = $data['status'] ?? 'active';
            
            // Validate required fields
            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                throw new Exception("Required fields missing: first_name, last_name, email, and password are required");
            }
            
            // Validate status is in allowed enum values (if status column exists)
            if ($hasStatus) {
                $allowedStatuses = ['active', 'inactive', 'suspended'];
                if (!in_array($status, $allowedStatuses)) {
                    $status = 'active'; // Default to active if invalid
                }
            }

            // Build dynamic INSERT statement based on existing columns
            $columns = ['first_name', 'last_name', 'email', 'password'];
            $placeholders = ['?', '?', '?', '?'];
            $values = [
                $firstName,
                $lastName,
                $email,
                $password
            ];

            if ($hasUserId) {
                array_unshift($columns, 'user_id');
                array_unshift($placeholders, '?');
                array_unshift($values, $userId);
            }

            if ($hasPhoneStudent) {
                $columns[] = 'phone';
                $placeholders[] = '?';
                $values[] = $data['phone'] ?? null;
            }

            if ($hasStudentNumber) {
                $columns[] = 'student_number';
                $placeholders[] = '?';
                $values[] = $data['student_number'] ?? null;
            }

            if ($hasYearEnrolled) {
                $columns[] = 'year_enrolled';
                $placeholders[] = '?';
                $values[] = $data['year_enrolled'] ?? null;
            }

            if ($hasMajor) {
                $columns[] = 'major';
                $placeholders[] = '?';
                $values[] = $data['major'] ?? null;
            }

            if ($hasMinor) {
                $columns[] = 'minor';
                $placeholders[] = '?';
                $values[] = $data['minor'] ?? null;
            }

            if ($hasGpa) {
                $columns[] = 'gpa';
                $placeholders[] = '?';
                $values[] = $data['gpa'] ?? null;
            }

            if ($hasTranscript) {
                $columns[] = 'transcript';
                $placeholders[] = '?';
                $values[] = $data['transcript'] ?? null;
            }

            if ($hasStatus) {
                $columns[] = 'status';
                $placeholders[] = '?';
                $values[] = $status;
            }

            // Add created_at and updated_at (use NOW() directly in SQL, not as placeholder)
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
            $placeholders[] = 'NOW()';
            $placeholders[] = 'NOW()';

            // Build the SQL statement
            $sql = "INSERT INTO students (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $studentStmt = $this->db->prepare($sql);
            
            // Execute with only the ? placeholder values (excluding NOW() which is in SQL)
            $studentStmt->execute($values);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $errorInfo = $e->errorInfo ?? [];
            $errorDetails = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'sqlstate' => $errorInfo[0] ?? 'N/A',
                'driver_code' => $errorInfo[1] ?? 'N/A',
                'driver_message' => $errorInfo[2] ?? 'N/A'
            ];
            error_log('Create student PDO error: ' . json_encode($errorDetails));
            // Create a more detailed exception - ensure code is an integer
            $errorCode = is_numeric($e->getCode()) ? (int)$e->getCode() : 0;
            $detailedError = new PDOException(
                'Database error: ' . $e->getMessage() . 
                ' | SQL State: ' . ($errorInfo[0] ?? 'N/A') . 
                ' | Driver Code: ' . ($errorInfo[1] ?? 'N/A') . 
                ' | Driver Message: ' . ($errorInfo[2] ?? $e->getMessage()),
                $errorCode
            );
            $detailedError->errorInfo = $errorInfo;
            throw $detailedError;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Create student error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a doctor (admin action)
     */
    public function createDoctor($data) {
        $this->db->beginTransaction();
        try {
            // Validate required fields
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                throw new Exception("Required fields missing: first_name, last_name, and email are required");
            }

            // Department is required in the database schema (NOT NULL)
            if (empty($data['department'])) {
                $data['department'] = 'General'; // Default department if not provided
            }

            // Check which columns exist in doctors table
            $columnsStmt = $this->db->query("SHOW COLUMNS FROM doctors");
            $existingColumns = [];
            while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                $existingColumns[] = $row['Field'];
            }

            $hasUserId = in_array('user_id', $existingColumns);
            $hasEmployeeId = in_array('employee_id', $existingColumns);
            $hasPhone = in_array('phone', $existingColumns);
            $hasBio = in_array('bio', $existingColumns);

            $userId = null;
            if ($hasUserId) {
                // First create user entry if user_id column exists
                $userStmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, 'user', NOW(), NOW())
                ");
                $userStmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null,
                ]);
                $userId = $this->db->lastInsertId();

                if (!$userId || $userId == 0) {
                    throw new Exception("Failed to get user ID after insert");
                }
            }

            // Build dynamic INSERT statement based on existing columns
            $columns = ['first_name', 'last_name', 'email', 'password', 'department'];
            $placeholders = ['?', '?', '?', '?', '?'];
            $values = [
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['email'] ?? null,
                $data['password'] ?? null,
                $data['department'] ?? 'General'
            ];

            if ($hasUserId) {
                array_unshift($columns, 'user_id');
                array_unshift($placeholders, '?');
                array_unshift($values, $userId);
            }

            if ($hasPhone) {
                $columns[] = 'phone';
                $placeholders[] = '?';
                $values[] = $data['phone'] ?? null;
            }

            if ($hasEmployeeId) {
                $columns[] = 'employee_id';
                $placeholders[] = '?';
                $values[] = $data['employee_id'] ?? null;
            }

            if ($hasBio) {
                $columns[] = 'bio';
                $placeholders[] = '?';
                $values[] = $data['bio'] ?? null;
            }

            // Add created_at and updated_at (use NOW() directly in SQL, not as placeholder)
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
            $placeholders[] = 'NOW()';
            $placeholders[] = 'NOW()';

            // Build the SQL statement
            $sql = "INSERT INTO doctors (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $doctorStmt = $this->db->prepare($sql);
            
            // Execute with only the ? placeholder values (excluding NOW() which is in SQL)
            $doctorStmt->execute($values);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Create doctor error: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Create doctor error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a doctor by id
     */
    public function updateDoctor($id, $data) {
        $fields = [];
        $params = [];
        $allowed = ['first_name', 'last_name', 'email', 'phone', 'department', 'bio', 'employee_id'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE doctors SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a doctor by id (cascades to users table via foreign key)
     */
    public function deleteDoctor($id) {
        $stmt = $this->db->prepare("DELETE FROM doctors WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Create an IT officer (admin action)
     */
    public function createITOfficer($data) {
        $this->db->beginTransaction();
        try {
            // Validate required fields
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                throw new Exception("Required fields missing: first_name, last_name, and email are required");
            }

            // Check which columns exist in it_officers table
            try {
                $columnsStmt = $this->db->query("SHOW COLUMNS FROM it_officers");
                $existingColumns = [];
                while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $existingColumns[] = $row['Field'];
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                throw new Exception("Cannot access it_officers table: " . $e->getMessage());
            }

            $hasUserId = in_array('user_id', $existingColumns);
            $hasPhone = in_array('phone', $existingColumns);
            $hasEmployeeId = in_array('employee_id', $existingColumns);
            $hasDepartment = in_array('department', $existingColumns);
            $hasSpecialization = in_array('specialization', $existingColumns);
            $hasOfficeLocation = in_array('office_location', $existingColumns);

            $userId = null;
            if ($hasUserId) {
                // First create user entry if user_id column exists
                $userStmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, 'user', NOW(), NOW())
                ");
                $userStmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null,
                ]);
                $userId = $this->db->lastInsertId();

                if (!$userId || $userId == 0) {
                    throw new Exception("Failed to get user ID after insert");
                }
            }

            // Build dynamic INSERT statement based on existing columns
            $columns = ['first_name', 'last_name', 'email', 'password'];
            $placeholders = ['?', '?', '?', '?'];
            $values = [
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['email'] ?? null,
                $data['password'] ?? null
            ];

            if ($hasUserId) {
                array_unshift($columns, 'user_id');
                array_unshift($placeholders, '?');
                array_unshift($values, $userId);
            }

            if ($hasPhone) {
                $columns[] = 'phone';
                $placeholders[] = '?';
                $values[] = $data['phone'] ?? null;
            }

            if ($hasEmployeeId) {
                $columns[] = 'employee_id';
                $placeholders[] = '?';
                $values[] = $data['employee_id'] ?? null;
            }

            if ($hasDepartment) {
                $columns[] = 'department';
                $placeholders[] = '?';
                $values[] = $data['department'] ?? null;
            }

            if ($hasSpecialization) {
                $columns[] = 'specialization';
                $placeholders[] = '?';
                $values[] = $data['specialization'] ?? null;
            }

            if ($hasOfficeLocation) {
                $columns[] = 'office_location';
                $placeholders[] = '?';
                $values[] = $data['office_location'] ?? null;
            }

            // Add created_at and updated_at (use NOW() directly in SQL, not as placeholder)
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
            $placeholders[] = 'NOW()';
            $placeholders[] = 'NOW()';

            // Build the SQL statement
            $sql = "INSERT INTO it_officers (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $itStmt = $this->db->prepare($sql);
            
            // Execute with only the ? placeholder values (excluding NOW() which is in SQL)
            $itStmt->execute($values);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Create IT officer error: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Create IT officer error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an IT officer by id
     */
    public function updateITOfficer($id, $data) {
        $fields = [];
        $params = [];
        $allowed = ['first_name', 'last_name', 'email', 'phone', 'department', 'specialization', 'employee_id'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE it_officers SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete an IT officer by id (cascades to users table via foreign key)
     */
    public function deleteITOfficer($id) {
        $stmt = $this->db->prepare("DELETE FROM it_officers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Create an advisor (admin action)
     */
    public function createAdvisor($data) {
        $this->db->beginTransaction();
        try {
            // Validate required fields
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                throw new Exception("Required fields missing: first_name, last_name, and email are required");
            }

            // Check which columns exist in advisors table
            try {
                $columnsStmt = $this->db->query("SHOW COLUMNS FROM advisors");
                $existingColumns = [];
                while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $existingColumns[] = $row['Field'];
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                throw new Exception("Cannot access advisors table: " . $e->getMessage());
            }

            $hasUserId = in_array('user_id', $existingColumns);
            $hasPhone = in_array('phone', $existingColumns);
            $hasEmployeeId = in_array('employee_id', $existingColumns);
            $hasDepartment = in_array('department', $existingColumns);
            $hasSpecialization = in_array('specialization', $existingColumns);
            $hasMaxStudents = in_array('max_students', $existingColumns);
            $hasOfficeLocation = in_array('office_location', $existingColumns);
            $hasOfficeHours = in_array('office_hours', $existingColumns);

            $userId = null;
            if ($hasUserId) {
                // First create user entry if user_id column exists
                $userStmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, 'user', NOW(), NOW())
                ");
                $userStmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null,
                ]);
                $userId = $this->db->lastInsertId();

                if (!$userId || $userId == 0) {
                    throw new Exception("Failed to get user ID after insert");
                }
            }

            // Build dynamic INSERT statement based on existing columns
            $columns = ['first_name', 'last_name', 'email', 'password'];
            $placeholders = ['?', '?', '?', '?'];
            $values = [
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['email'] ?? null,
                $data['password'] ?? null
            ];

            if ($hasUserId) {
                array_unshift($columns, 'user_id');
                array_unshift($placeholders, '?');
                array_unshift($values, $userId);
            }

            if ($hasPhone) {
                $columns[] = 'phone';
                $placeholders[] = '?';
                $values[] = $data['phone'] ?? null;
            }

            if ($hasEmployeeId) {
                $columns[] = 'employee_id';
                $placeholders[] = '?';
                $values[] = $data['employee_id'] ?? null;
            }

            if ($hasDepartment) {
                $columns[] = 'department';
                $placeholders[] = '?';
                $values[] = $data['department'] ?? null;
            }

            if ($hasSpecialization) {
                $columns[] = 'specialization';
                $placeholders[] = '?';
                $values[] = $data['specialization'] ?? null;
            }

            if ($hasMaxStudents) {
                $columns[] = 'max_students';
                $placeholders[] = '?';
                $values[] = $data['max_students'] ?? 50;
            }

            if ($hasOfficeLocation) {
                $columns[] = 'office_location';
                $placeholders[] = '?';
                $values[] = $data['office_location'] ?? null;
            }

            if ($hasOfficeHours) {
                $columns[] = 'office_hours';
                $placeholders[] = '?';
                $values[] = $data['office_hours'] ?? null;
            }

            // Add created_at and updated_at (use NOW() directly in SQL, not as placeholder)
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
            $placeholders[] = 'NOW()';
            $placeholders[] = 'NOW()';

            // Build the SQL statement
            $sql = "INSERT INTO advisors (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $advisorStmt = $this->db->prepare($sql);
            
            // Execute with only the ? placeholder values (excluding NOW() which is in SQL)
            $advisorStmt->execute($values);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Create advisor error: ' . $e->getMessage());
            throw $e; // Re-throw so controller can catch it
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Create advisor error: ' . $e->getMessage());
            throw $e; // Re-throw so controller can catch it
        }
    }

    /**
     * Update an advisor by id
     */
    public function updateAdvisor($id, $data) {
        $fields = [];
        $params = [];
        $allowed = ['first_name', 'last_name', 'email', 'phone', 'department', 'specialization', 'max_students', 'employee_id'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE advisors SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete an advisor by id (cascades to users table via foreign key)
     */
    public function deleteAdvisor($id) {
        $stmt = $this->db->prepare("DELETE FROM advisors WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Create an admin (admin action)
     */
    public function createAdmin($data) {
        $this->db->beginTransaction();
        try {
            // Validate required fields
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                throw new Exception("Required fields missing: first_name, last_name, and email are required");
            }

            // Check if admins table has user_id column
            $checkUserId = $this->db->query("SHOW COLUMNS FROM admins LIKE 'user_id'");
            $hasUserId = $checkUserId->rowCount() > 0;

            $userId = null;
            if ($hasUserId) {
                // First create user entry if user_id column exists
                $userStmt = $this->db->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, 'user', NOW(), NOW())
                ");
                $userStmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null,
                ]);
                $userId = $this->db->lastInsertId();

                if (!$userId || $userId == 0) {
                    throw new Exception("Failed to get user ID after insert");
                }

                // Create admin entry with user_id
                $adminStmt = $this->db->prepare("
                    INSERT INTO admins (user_id, first_name, last_name, email, phone, password, 
                                       employee_id, department, permissions, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $adminStmt->execute([
                    $userId,
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null,
                    $data['employee_id'] ?? null,
                    $data['department'] ?? null,
                    $data['permissions'] ?? null
                ]);
            } else {
                // Create admin entry without user_id (standalone table)
                $adminStmt = $this->db->prepare("
                    INSERT INTO admins (first_name, last_name, email, phone, password, 
                                       employee_id, department, permissions, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $adminStmt->execute([
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['password'] ?? null,
                    $data['employee_id'] ?? null,
                    $data['department'] ?? null,
                    $data['permissions'] ?? null
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Create admin error: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Create admin error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user by email (searches all role tables)
     */
    public function findByEmail($email) {
        // Check students
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, 'student' as role, created_at FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if ($result) return $result;
        
        // Check doctors
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, 'doctor' as role, created_at FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if ($result) return $result;
        
        // Check IT officers
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, 'it' as role, created_at FROM it_officers WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if ($result) return $result;
        
        // Check advisors
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, 'advisor' as role, created_at FROM advisors WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if ($result) return $result;
        
        // Check admins
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, 'admin' as role, created_at FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if ($result) return $result;
        
        // Check default users
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password, 'user' as role, created_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Get user by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Generate and store password reset code
     */
    public function generateResetCode($email) {
        // Generate a 6-digit numeric reset code
        $resetCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        // Set expiration to 30 minutes from now
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_reset_code = ?, password_reset_expires = ? 
            WHERE email = ?
        ");
        
        $result = $stmt->execute([$resetCode, $expiresAt, $email]);
        
        if ($result) {
            return $resetCode;
        }
        return false;
    }

    /**
     * Verify password reset code
     */
    public function verifyResetCode($email, $code) {
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE email = ? 
            AND password_reset_code = ? 
            AND password_reset_expires > NOW()
        ");
        $stmt->execute([$email, $code]);
        return $stmt->fetch() !== false;
    }

    /**
     * Update user password
     */
    public function updatePassword($email, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = ?, password_reset_code = NULL, password_reset_expires = NULL 
            WHERE email = ?
        ");
        
        return $stmt->execute([$hashedPassword, $email]);
    }

    /**
     * Clear reset code for an email
     */
    public function clearResetCode($email) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_reset_code = NULL, password_reset_expires = NULL 
            WHERE email = ?
        ");
        
        return $stmt->execute([$email]);
    }
}

