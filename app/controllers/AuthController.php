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
        $password = $input['password'];
        $confirmPassword = $input['confirmPassword'];

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
                'password' => $hashedPassword
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
}

