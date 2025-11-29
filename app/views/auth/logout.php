<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - University Portal</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .logout-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            padding: 2rem;
        }
        
        .logout-card {
            background-color: var(--surface-color);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 20px 40px var(--shadow-color);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .logout-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .logout-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .logout-message {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .logout-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .countdown {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .progress-fill {
            height: 100%;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }
        
        .session-info {
            background-color: var(--background-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .session-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .session-item:last-child {
            margin-bottom: 0;
        }
        
        .session-label {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .session-value {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" title="Switch to dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Logout Container -->
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            
            <h1 class="logout-title">Logging Out</h1>
            <p class="logout-message">
                You are being logged out of your account. All your session data will be cleared and you'll be redirected to the login page.
            </p>
            
            <!-- Session Information -->
            <div class="session-info">
                <div class="session-item">
                    <span class="session-label">User:</span>
                    <span class="session-value">John Doe</span>
                </div>
                <div class="session-item">
                    <span class="session-label">Role:</span>
                    <span class="session-value">Student</span>
                </div>
                <div class="session-item">
                    <span class="session-label">Last Activity:</span>
                    <span class="session-value">2 minutes ago</span>
                </div>
                <div class="session-item">
                    <span class="session-label">Session Duration:</span>
                    <span class="session-value">2 hours 15 minutes</span>
                </div>
            </div>
            
            <!-- Countdown -->
            <div class="countdown" id="countdown">Redirecting in 5 seconds...</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 100%;"></div>
            </div>
            
            <!-- Actions -->
            <div class="logout-actions">
                <button class="btn btn-primary" onclick="cancelLogout()">
                    <i class="fas fa-times"></i>
                    Cancel Logout
                </button>
                <button class="btn btn-outline" onclick="logoutNow()">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout Now
                </button>
            </div>
            
            <!-- Additional Options -->
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Additional Options</h3>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <button class="btn btn-outline" onclick="logoutAllDevices()" style="width: 100%;">
                        <i class="fas fa-mobile-alt"></i>
                        Logout from All Devices
                    </button>
                    <button class="btn btn-outline" onclick="changePassword()" style="width: 100%;">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/main.js"></script>
    <script>
        let countdown = 5;
        let countdownInterval;
        let progressInterval;
        
        // Start countdown
        function startCountdown() {
            countdownInterval = setInterval(() => {
                countdown--;
                document.getElementById('countdown').textContent = `Redirecting in ${countdown} seconds...`;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    clearInterval(progressInterval);
                    performLogout();
                }
            }, 1000);
            
            // Progress bar animation
            let progress = 100;
            progressInterval = setInterval(() => {
                progress -= 20;
                document.getElementById('progressFill').style.width = progress + '%';
            }, 1000);
        }
        
        // Cancel logout
        function cancelLogout() {
            clearInterval(countdownInterval);
            clearInterval(progressInterval);
            showNotification('Logout cancelled', 'info');
            setTimeout(() => {
                window.location.href = '../student/student_dashboard.html';
            }, 1000);
        }
        
        // Logout now
        function logoutNow() {
            clearInterval(countdownInterval);
            clearInterval(progressInterval);
            performLogout();
        }
        
        // Perform logout
        function performLogout() {
            showNotification('Logging out...', 'info');
            
            // Simulate logout process
            setTimeout(() => {
                showNotification('Successfully logged out', 'success');
                setTimeout(() => {
                    window.location.href = '../auth/auth_login.html';
                }, 1000);
            }, 1500);
        }
        
        // Logout from all devices
        function logoutAllDevices() {
            if (confirm('Are you sure you want to logout from all devices? This will end all active sessions.')) {
                showNotification('Logging out from all devices...', 'info');
                setTimeout(() => {
                    showNotification('All sessions terminated', 'success');
                    setTimeout(() => {
                        window.location.href = '../auth/auth_login.html';
                    }, 1000);
                }, 2000);
            }
        }
        
        // Change password
        function changePassword() {
            showNotification('Redirecting to password change page...', 'info');
            setTimeout(() => {
                window.location.href = 'settings.html#security';
            }, 1000);
        }
        
        // Start countdown when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
        });
        
        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Pause countdown when page is hidden
                clearInterval(countdownInterval);
                clearInterval(progressInterval);
            } else {
                // Resume countdown when page becomes visible
                startCountdown();
            }
        });
        
        // Handle browser back button
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your logout process will be cancelled.';
        });
    </script>
</body>
</html>
