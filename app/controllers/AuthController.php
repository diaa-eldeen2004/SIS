<?php

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    /**
     * Handle user registration
     */
    public function signup() {
        // Set content type to JSON
        header('Content-Type: application/json');

        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        // If JSON decode failed, try form data
        if ($input === null) {
            $input = $_POST;
        }

        // Validate required fields
        $required = ['firstName', 'lastName', 'email', 'password', 'confirmPassword'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => ucfirst($field) . ' is required'
                ]);
                return;
            }
        }

        $firstName = trim($input['firstName']);
        $lastName = trim($input['lastName']);
        $email = trim($input['email']);
        $phone = isset($input['phone']) ? trim($input['phone']) : '';
        $password = $input['password'];
        $confirmPassword = $input['confirmPassword'];

        // Force role to 'user' for all public signups - only admins can create other roles
        $role = 'user';

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email format'
            ]);
            return;
        }

        // Validate password match
        if ($password !== $confirmPassword) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Passwords do not match'
            ]);
            return;
        }

        // Validate password strength
        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Password must be at least 8 characters long'
            ]);
            return;
        }

        // Check if email already exists
        if ($this->user->emailExists($email)) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Email already registered'
            ]);
            return;
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Create user
        try {
            $success = $this->user->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashedPassword,
                'role' => $role
            ]);

            if ($success) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Account created successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create account. Please try again.'
                ]);
            }
        } catch (Exception $e) {
            error_log("Signup error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    /**
     * Handle user login
     */
    public function login() {
        // Set content type to JSON
        header('Content-Type: application/json');

        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        // If JSON decode failed, try form data
        if ($input === null) {
            $input = $_POST;
        }

        // Validate required fields
        if (empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
            return;
        }

        $email = trim($input['email']);
        $password = $input['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email format'
            ]);
            return;
        }

        try {
            // Find user by email
            $user = $this->user->findByEmail($email);
            
            if (!$user) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
                return;
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
                return;
            }

            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Store user data in session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user',
                'created_at' => $user['created_at']
            ];

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'user'
                ]
            ]);
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    /**
     * Handle password reset request (send email with code)
     */
    public function requestPasswordReset() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            $input = $_POST;
        }

        if (empty($input['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            return;
        }

        $email = trim($input['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }

        try {
            // Check if user exists
            $user = $this->user->findByEmail($email);
            if (!$user) {
                // For security, return generic message (don't reveal if email exists)
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'If an account with that email exists, a reset code will be sent.'
                ]);
                return;
            }

            // Generate reset code
            $resetCode = $this->user->generateResetCode($email);
            if (!$resetCode) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to generate reset code']);
                return;
            }

            // Attempt to send email with reset code
            $mailSent = $this->sendPasswordResetEmail($email, $user['first_name'], $resetCode);
            
            // Log reset code for debugging (in case email fails)
            error_log("Password Reset Code for $email: $resetCode (Expires: " . date('Y-m-d H:i:s', time() + 30 * 60) . ")");
            
            // Return success regardless of email delivery (code is in DB, accessible via logs)
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Reset code sent! Check your email, or check the server logs for the code during testing.'
            ]);
            
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verify password reset code and allow password change
     */
    public function resetPassword() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            $input = $_POST;
        }

        $required = ['email', 'code', 'newPassword', 'confirmPassword'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
                return;
            }
        }

        $email = trim($input['email']);
        $code = trim($input['code']);
        $newPassword = $input['newPassword'];
        $confirmPassword = $input['confirmPassword'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }

        // Validate password match
        if ($newPassword !== $confirmPassword) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
            return;
        }

        try {
            // Verify reset code
            if (!$this->user->verifyResetCode($email, $code)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid or expired reset code. Please request a new one.'
                ]);
                return;
            }

            // Update password and clear reset code
            if ($this->user->updatePassword($email, $newPassword)) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Password reset successfully. You can now login with your new password.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update password. Please try again.'
                ]);
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    /**
     * Send password reset email. Prefer PHPMailer via Composer (vendor/autoload.php) and SMTP config in app/config/mail.php.
     * Falls back to PHP mail() and local logging if PHPMailer isn't installed.
     */
    private function sendPasswordResetEmail($email, $firstName, $resetCode) {
        $to = $email;
        $subject = 'Password Reset Code - University Portal';

        // Build HTML email body
        $htmlBody = '<html><head><style>';
        $htmlBody .= 'body { font-family: "Inter", Arial, sans-serif; line-height: 1.6; color: #333; }';
        $htmlBody .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }';
        $htmlBody .= '.header { background-color: #2563eb; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }';
        $htmlBody .= '.content { background-color: white; padding: 30px; border-radius: 0 0 8px 8px; }';
        $htmlBody .= '.code-box { background-color: #f0f0f0; border: 2px solid #2563eb; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }';
        $htmlBody .= '.code { font-size: 32px; font-weight: bold; color: #2563eb; letter-spacing: 2px; }';
        $htmlBody .= '.footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }';
        $htmlBody .= 'a { color: #2563eb; text-decoration: none; }';
        $htmlBody .= 'a:hover { text-decoration: underline; }';
        $htmlBody .= '</style></head><body>';
        $htmlBody .= '<div class="container">';
        $htmlBody .= '<div class="header"><h1>Password Reset Request</h1></div>';
        $htmlBody .= '<div class="content">';
        $htmlBody .= '<p>Hi <strong>' . htmlspecialchars($firstName) . '</strong>,</p>';
        $htmlBody .= '<p>We received a request to reset your password for your University Portal account. Use the code below to reset your password.</p>';
        $htmlBody .= '<div class="code-box"><div class="code">' . htmlspecialchars($resetCode) . '</div></div>';
        $htmlBody .= '<p><strong>Important:</strong> This code will expire in 30 minutes for security reasons.</p>';
        $htmlBody .= '<p>If you did not request a password reset, please ignore this email or contact our support team.</p>';
        $htmlBody .= '</div>';
        $htmlBody .= '<div class="footer">';
        $htmlBody .= '<p>&copy; 2024 University Portal. All rights reserved.</p>';
        $htmlBody .= '</div></div></body></html>';

        // Try to use PHPMailer if Composer autoload exists and mail config is available
        $projectRoot = realpath(__DIR__ . '/..');
        $autoload = $projectRoot . '/vendor/autoload.php';
        $mailConfigFile = __DIR__ . '/../config/mail.php';

        if (file_exists($autoload)) {
            try {
                require_once $autoload;

                // Load mail config if present
                $mailConfig = null;
                if (file_exists($mailConfigFile)) {
                    $mailConfig = require $mailConfigFile;
                }

                // If PHPMailer available, use it
                if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    try {
                        // Configure SMTP if config provided
                        if (is_array($mailConfig) && !empty($mailConfig['smtp_host'])) {
                            $mail->isSMTP();
                            $mail->Host = $mailConfig['smtp_host'];
                            $mail->SMTPAuth = !empty($mailConfig['smtp_auth']);
                            if (!empty($mailConfig['smtp_username'])) {
                                $mail->Username = $mailConfig['smtp_username'];
                            }
                            if (!empty($mailConfig['smtp_password'])) {
                                $mail->Password = $mailConfig['smtp_password'];
                            }
                            $mail->SMTPSecure = $mailConfig['smtp_secure'] ?? '';
                            $mail->Port = $mailConfig['smtp_port'] ?? 587;
                        }

                        $fromEmail = $mailConfig['from_email'] ?? 'noreply@university.edu';
                        $fromName = $mailConfig['from_name'] ?? 'University Portal';

                        $mail->setFrom($fromEmail, $fromName);
                        $mail->addAddress($to, $firstName ?: '');
                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body = $htmlBody;
                        $mail->AltBody = 'Your password reset code: ' . $resetCode;

                        $sent = $mail->send();
                        if ($sent) {
                            error_log("Password reset email sent via PHPMailer to: $email");
                        } else {
                            error_log("PHPMailer failed to send to: $email");
                        }

                        // still write reset code to local log for dev convenience
                        try {
                            $logDir = __DIR__ . '/../logs';
                            if (!is_dir($logDir)) {
                                @mkdir($logDir, 0755, true);
                            }
                            $logFile = $logDir . '/reset_codes.log';
                            $logEntry = date('Y-m-d H:i:s') . " | $email | $resetCode\n";
                            @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
                        } catch (Exception $e) {
                            // ignore file logging errors
                        }

                        return (bool)$sent;
                    } catch (\PHPMailer\PHPMailer\Exception $ex) {
                        error_log('PHPMailer Exception: ' . $ex->getMessage());
                        // fall through to fallback mail() below
                    }
                }
            } catch (Exception $e) {
                error_log('Autoload/PHPMailer include error: ' . $e->getMessage());
                // fall back to mail()
            }
        }

        // Fallback: use PHP mail() and local logging (useful for local dev where PHPMailer isn't installed yet)
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@university.edu\r\n";

        try {
            $result = mail($to, $subject, $htmlBody, $headers);

            // Log email attempt for debugging
            if (!$result) {
                error_log("Email send failed for: $email with code: $resetCode");
            } else {
                error_log("Password reset email sent to: $email");
            }

            // Also write reset code to a local log file for developer testing
            try {
                $logDir = __DIR__ . '/../logs';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0755, true);
                }
                $logFile = $logDir . '/reset_codes.log';
                $logEntry = date('Y-m-d H:i:s') . " | $email | $resetCode\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            } catch (Exception $e) {
                // ignore file logging errors but keep original error_log entries
            }

            return $result;
        } catch (Exception $e) {
            error_log("Email exception for $email: " . $e->getMessage());
            return false;
        }
    }
}

