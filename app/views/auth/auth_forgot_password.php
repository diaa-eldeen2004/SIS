<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - University Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="reset-password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <a href="../home.php" class="back-home"><i class="fas fa-arrow-left"></i>Back</a>
    <div class="auth-container">
        <div class="auth-card">
            <div class="step-indicator">
                <div class="step active" id="step1-indicator">
                    <div class="step-circle">1</div>
                    <div class="step-label">Email</div>
                </div>
                <div class="step" id="step2-indicator">
                    <div class="step-circle">2</div>
                    <div class="step-label">Verify</div>
                </div>
                <div class="step" id="step3-indicator">
                    <div class="step-circle">3</div>
                    <div class="step-label">Reset</div>
                </div>
            </div>
            <div id="step1" class="reset-step">
                <div class="auth-header">
                    <div class="auth-logo"><i class="fas fa-key"></i></div>
                    <h1 class="auth-title">Reset Password</h1>
                    <p class="auth-subtitle">Enter your email to receive a reset code</p>
                </div>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <span>We'll send a 6-digit code to your email (expires in 30 minutes)</span>
                </div>
                <form id="step1-form">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-reset">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Reset Code</span>
                    </button>
                </form>
                <a href="auth_login.php" class="back-link"><i class="fas fa-arrow-left"></i>Back to Login</a>
            </div>
            <div id="step2" class="reset-step hidden">
                <div class="auth-header">
                    <div class="auth-logo"><i class="fas fa-shield-alt"></i></div>
                    <h1 class="auth-title">Verify Code</h1>
                    <p class="auth-subtitle">Enter the 6-digit code sent to your email</p>
                </div>
                <div class="info-box">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Code sent to:</strong><br>
                        <span id="verifyEmail"></span>
                    </div>
                </div>
                <form id="step2-form">
                    <div class="form-group">
                        <label class="form-label">Verification Code</label>
                        <div class="code-input-group" id="codeInputGroup">
                            <input type="text" class="code-input" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="code-input" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="code-input" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="code-input" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="code-input" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="code-input" maxlength="1" inputmode="numeric" required>
                        </div>
                        <input type="hidden" id="fullCode" name="code">
                    </div>
                    <button type="submit" class="btn-reset">
                        <i class="fas fa-check"></i>
                        <span>Verify Code</span>
                    </button>
                </form>
                <div class="resend-timer">
                    <p id="timerText">Didn't receive code? 
                        <button type="button" id="resendBtn" class="btn-reset btn-resend">Resend</button>
                    </p>
                </div>
                <button type="button" class="back-link" onclick="goToStep1()"><i class="fas fa-arrow-left"></i>Change Email</button>
            </div>
            <div id="step3" class="reset-step hidden">
                <div class="auth-header">
                    <div class="auth-logo"><i class="fas fa-lock"></i></div>
                    <h1 class="auth-title">Create New Password</h1>
                    <p class="auth-subtitle">Enter your new password</p>
                </div>
                <form id="step3-form">
                    <div class="form-group">
                        <label class="form-label" for="newPassword">New Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="newPassword" name="newPassword" class="form-input" placeholder="Create new password" required>
                        </div>
                        <div class="password-strength">
                            <div id="strengthText">Password strength</div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="Confirm password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-reset">
                        <i class="fas fa-save"></i>
                        <span>Reset Password</span>
                    </button>
                </form>
                <a href="auth_login.php" class="back-link"><i class="fas fa-sign-in-alt"></i>Back to Login</a>
            </div>
        </div>
    </div>
    <script src="../js/main.js"></script>
    <script src="reset-password.js"></script>
</body>
</html>
