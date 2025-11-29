<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - University Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            padding: 2rem;
        }
        
        .auth-card {
            background-color: var(--surface-color);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 20px 40px var(--shadow-color);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .auth-header {
            margin-bottom: 2rem;
        }
        
        .auth-logo {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--background-color);
            color: var(--text-primary);
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .input-group .form-input {
            padding-left: 3rem;
        }
        
        .btn-signup {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .btn-signup:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }
        
        .auth-links {
            margin-top: 1.5rem;
        }
        
        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .auth-links a:hover {
            color: var(--accent-color);
        }
        
        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .back-home:hover {
            color: var(--accent-color);
        }
        
        .back-home i {
            margin-right: 0.5rem;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }
        
        .strength-bar {
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
            margin-top: 0.25rem;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-weak { background-color: var(--error-color); }
        .strength-medium { background-color: var(--warning-color); }
        .strength-strong { background-color: var(--success-color); }
        
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .terms-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
            margin-top: 0.2rem;
        }
        
        .terms-checkbox label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }
        
        .terms-checkbox a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .terms-checkbox a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" title="Switch to dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Back to Home -->
    <a href="../index.html" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Back to Home
    </a>

    <!-- Auth Container -->
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join our university community</p>
            </div>

            <form id="signupForm" class="auth-form">
                <!-- Name Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="firstName">First Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="firstName" name="firstName" class="form-input" placeholder="First name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="lastName">Last Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="lastName" name="lastName" class="form-input" placeholder="Last name" required>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
                    </div>
                </div>

                <!-- Role Selector -->
                <div class="form-group">
                    <label class="form-label" for="role">Select Role</label>
                    <div class="input-group">
                        <i class="fas fa-user-tag"></i>
                        <select id="role" name="role" class="form-input">
                            <option value="admin">Admin</option>
                            <option value="user" selected>User</option>
                            <option value="student">Student</option>
                            <option value="advisor">Advisor</option>
                            <option value="doctor">Doctor</option>
                            <option value="it">IT</option>
                        </select>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Create a password" required>
                    </div>
                    <div class="password-strength">
                        <div id="strengthText">Password strength</div>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="Confirm your password" required>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn-signup">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="auth_login.php">Sign in here</a></p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
        <script src="../js/main.js"></script>
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.getElementById('strengthText');
            const strengthFill = document.getElementById('strengthFill');
            
            let strength = 0;
            let strengthLabel = '';
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (strength < 2) {
                strengthLabel = 'Weak';
                strengthFill.className = 'strength-fill strength-weak';
                strengthFill.style.width = '20%';
            } else if (strength < 4) {
                strengthLabel = 'Medium';
                strengthFill.className = 'strength-fill strength-medium';
                strengthFill.style.width = '60%';
            } else {
                strengthLabel = 'Strong';
                strengthFill.className = 'strength-fill strength-strong';
                strengthFill.style.width = '100%';
            }
            
            strengthText.textContent = `Password strength: ${strengthLabel}`;
        });

        // Form submission
        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirmPassword');
            
            // Validate password match
            if (password !== confirmPassword) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            
            // Validate password strength
            if (password.length < 8) {
                showNotification('Password must be at least 8 characters long', 'error');
                return;
            }
            
            // Basic validation
            const requiredFields = ['firstName', 'lastName', 'email', 'password', 'confirmPassword', 'role'];
            for (let field of requiredFields) {
                if (!formData.get(field)) {
                    showNotification('Please fill in all required fields', 'error');
                    return;
                }
            }
            
            // Disable submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            
            // Prepare data for API
            const data = {
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                email: formData.get('email'),
                password: password,
                confirmPassword: confirmPassword,
                role: formData.get('role') || 'user'
            };
            
            try {
                // Resolve API path relative to the current page
                // Current page: /sis/app/views/auth/auth_sign.php
                // Target: /sis/public/api/auth.php
                // Relative: ../../public/api/auth.php
                let apiPath = new URL('../../public/api/auth.php?action=signup', window.location.href).href;
                console.log('Signup API path (relative):', apiPath);

                let response = await fetch(apiPath, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                // If we got a 404, try fallback with absolute path
                if (response.status === 404) {
                    try {
                        const origin = window.location.origin;
                        const pathParts = window.location.pathname.split('/').filter(Boolean);
                        // Find 'sis' or first path segment as project root
                        let rootIndex = pathParts.indexOf('sis');
                        if (rootIndex === -1) rootIndex = 0;
                        const projectRoot = '/' + pathParts.slice(0, rootIndex + 1).join('/');
                        const altPath = origin + projectRoot + '/public/api/auth.php?action=signup';
                        console.log('Fallback signup API path:', altPath);
                        response = await fetch(altPath, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data)
                        });
                    } catch (e) {
                        console.warn('Fallback path failed', e);
                    }
                }

                console.log('Signup final API path used, status:', response.status);

                // Debug HTTP errors with body text (often HTML error pages)
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Signup HTTP error', response.status, text);
                    showNotification('Server error: ' + (response.statusText || response.status), 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Signup unexpected non-JSON response:', text);
                    showNotification('Unexpected server response. Check console for details.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    showNotification(result.message || 'Account created successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        // After successful signup route user to views/home.php (relative path)
                        window.location.href = '../home.php';
                    }, 2000);
                } else {
                    showNotification(result.message || 'Failed to create account. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Signup network error:', error);
                showNotification('An error occurred. Please try again later.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Real-time password confirmation validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = 'var(--error-color)';
            } else {
                this.style.borderColor = 'var(--border-color)';
            }
        });
    </script>
</body>
</html>
