let resetEmail = '';
let resetCode = '';
let resendTimer = 0;

// Step 1: Request Reset Code
document.getElementById('step1-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    resetEmail = document.getElementById('email').value;
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
        try {
        // Build a robust absolute API path (avoid relative paths that resolve under /sis/app)
        const origin = window.location.origin;
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        let rootIndex = pathParts.indexOf('sis');
        if (rootIndex === -1) rootIndex = 0;
        const projectRoot = '/' + pathParts.slice(0, rootIndex + 1).join('/');
        const apiPath = origin + projectRoot + '/public/api/auth.php?action=request-password-reset';
        console.log('Request reset code API path:', apiPath);

        let response = await fetch(apiPath, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: resetEmail })
        });

        // Handle non-JSON or error responses gracefully
        const contentType = response.headers.get('content-type') || '';
        if (!response.ok) {
            const text = await response.text();
            console.error('Error:', response.status, text);
            showNotification('Server error: ' + (response.statusText || response.status), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        let result;
        if (contentType.includes('application/json')) {
            result = await response.json();
        } else {
            // Received HTML or text (maybe an error page). Log and show a friendly message.
            const text = await response.text();
            console.warn('Non-JSON response received:', text);
            showNotification('Unexpected server response. Check server logs.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        if (result.success) {
            showNotification(result.message, 'success');
            document.getElementById('verifyEmail').textContent = resetEmail;
            goToStep2();
            startResendTimer();
        } else {
            showNotification(result.message || 'Failed to send reset code', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again later.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Code input auto-move
document.querySelectorAll('.code-input').forEach((input, index) => {
    input.addEventListener('input', function(e) {
        if (this.value.length === 1) {
            if (index < 5) {
                document.querySelectorAll('.code-input')[index + 1].focus();
            }
        }
        updateFullCode();
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
            document.querySelectorAll('.code-input')[index - 1].focus();
        }
    });
});

function updateFullCode() {
    const codes = Array.from(document.querySelectorAll('.code-input')).map(input => input.value);
    resetCode = codes.join('');
    document.getElementById('fullCode').value = resetCode;
}

// Step 2: Verify Code
document.getElementById('step2-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (resetCode.length !== 6) {
        showNotification('Please enter the complete 6-digit code', 'error');
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    
        try {
        // Build absolute API path
        const origin = window.location.origin;
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        let rootIndex = pathParts.indexOf('sis');
        if (rootIndex === -1) rootIndex = 0;
        const projectRoot = '/' + pathParts.slice(0, rootIndex + 1).join('/');
        const apiPath = origin + projectRoot + '/public/api/auth.php?action=reset-password';

        let response = await fetch(apiPath, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: resetEmail,
                code: resetCode,
                newPassword: 'temp',
                confirmPassword: 'temp'
            })
        });

        const contentType = response.headers.get('content-type') || '';
        if (!response.ok) {
            const text = await response.text();
            console.error('Error:', response.status, text);
            showNotification('Server error: ' + (response.statusText || response.status), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        if (!contentType.includes('application/json')) {
            const text = await response.text();
            console.warn('Non-JSON response received:', text);
            showNotification('Unexpected server response. Check server logs.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Code verified! Now set your new password.', 'success');
            goToStep3();
        } else {
            showNotification(result.message || 'Invalid or expired code', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again later.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Step 3: Reset Password
document.getElementById('step3-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }

    if (newPassword.length < 8) {
        showNotification('Password must be at least 8 characters long', 'error');
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
    
        try {
        // Build absolute API path
        const origin = window.location.origin;
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        let rootIndex = pathParts.indexOf('sis');
        if (rootIndex === -1) rootIndex = 0;
        const projectRoot = '/' + pathParts.slice(0, rootIndex + 1).join('/');
        const apiPath = origin + projectRoot + '/public/api/auth.php?action=reset-password';

        let response = await fetch(apiPath, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: resetEmail,
                code: resetCode,
                newPassword: newPassword,
                confirmPassword: confirmPassword
            })
        });

        const contentType = response.headers.get('content-type') || '';
        if (!response.ok) {
            const text = await response.text();
            console.error('Error:', response.status, text);
            showNotification('Server error: ' + (response.statusText || response.status), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        if (!contentType.includes('application/json')) {
            const text = await response.text();
            console.warn('Non-JSON response received:', text);
            showNotification('Unexpected server response. Check server logs.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Password reset successfully! Redirecting to login...', 'success');
            setTimeout(() => {
                window.location.href = 'auth_login.php';
            }, 2000);
        } else {
            showNotification(result.message || 'Failed to reset password', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again later.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Password strength checker
document.getElementById('newPassword').addEventListener('input', function() {
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

// Navigation functions
function goToStep1() {
    document.getElementById('step1').classList.remove('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.add('hidden');
    updateStepIndicator(1);
}

function goToStep2() {
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    document.getElementById('step3').classList.add('hidden');
    updateStepIndicator(2);
    document.querySelectorAll('.code-input')[0].focus();
}

function goToStep3() {
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.remove('hidden');
    updateStepIndicator(3);
}

function updateStepIndicator(step) {
    ['step1', 'step2', 'step3'].forEach((s, i) => {
        const indicator = document.getElementById(s + '-indicator');
        if (i + 1 < step) {
            indicator.classList.remove('active');
            indicator.classList.add('completed');
        } else if (i + 1 === step) {
            indicator.classList.add('active');
            indicator.classList.remove('completed');
        } else {
            indicator.classList.remove('active', 'completed');
        }
    });
}

function startResendTimer() {
    resendTimer = 60;
    const resendBtn = document.getElementById('resendBtn');
    resendBtn.disabled = true;
    
    const updateTimer = setInterval(() => {
        resendTimer--;
        if (resendTimer <= 0) {
            clearInterval(updateTimer);
            resendBtn.disabled = false;
            document.getElementById('timerText').innerHTML = 'Didn\'t receive the code? <button type="button" id="resendBtn" class="btn-reset" style="width: auto; padding: 0.5rem 1rem; margin: 0; display: inline-block;">Resend</button>';
            attachResendListener();
        } else {
            resendBtn.innerHTML = `Resend (${resendTimer}s)`;
        }
    }, 1000);
}

function attachResendListener() {
    document.getElementById('resendBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('step1-form').dispatchEvent(new Event('submit'));
        goToStep2();
    });
}

// Initial resend button listener
attachResendListener();
