const API_URL = '/api';
let captchaTokens = {};

document.addEventListener('DOMContentLoaded', () => {
    setupCaptchaListeners();
    checkAuthStatus();
    loadCaptchaChallenge('login');
    loadCaptchaChallenge('register');
});

function setupCaptchaListeners() {
    const registerUsername = document.getElementById('register-username');

    ['login', 'register'].forEach((type) => {
        const verifyButton = document.getElementById(`${type}-captcha-verify`);
        const refreshButton = document.getElementById(`${type}-captcha-refresh`);

        if (verifyButton) {
            verifyButton.addEventListener('click', () => verifyCaptcha(type));
        }

        if (refreshButton) {
            refreshButton.addEventListener('click', () => loadCaptchaChallenge(type));
        }
    });

    if (registerUsername) {
        registerUsername.addEventListener('blur', async (e) => {
            const username = e.target.value;
            if (username.length >= 3) await checkUsernameAvailability(username);
        });
    }
}

async function checkAuthStatus() {
    const token = localStorage.getItem('authToken');
    if (!token) return;

    try {
        const response = await axios.get(`${API_URL}/verify`, {
            headers: { Authorization: `Bearer ${token}` }
        });

        if (response.data.valid) {
            const loginSection = document.getElementById('login');
            if (loginSection) loginSection.style.display = 'none';
        }
    } catch (error) {
        localStorage.removeItem('authToken');
    }
}

async function loadCaptchaChallenge(type) {
    const promptEl = document.getElementById(`${type}-captcha-prompt`);
    const inputEl = document.getElementById(`${type}-captcha-answer`);

    if (!promptEl || !inputEl) return;

    try {
        showStatus(`${type}-captcha-status`, 'Loading challenge...', 'info');
        const response = await axios.get(`${API_URL}/captcha-challenge`, {
            params: { form: type }
        });

        promptEl.textContent = response.data.prompt;
        inputEl.value = '';
        captchaTokens[type] = null;
        showStatus(`${type}-captcha-status`, 'Solve the challenge and click Verify.', 'info');
    } catch (error) {
        showStatus(`${type}-captcha-status`, 'Unable to load captcha challenge', 'error');
    }
}

async function verifyCaptcha(type) {
    const inputEl = document.getElementById(`${type}-captcha-answer`);
    if (!inputEl) return;

    try {
        showStatus(`${type}-captcha-status`, 'Verifying...', 'info');

        const response = await axios.post(`${API_URL}/verify-captcha`, {
            form: type,
            answer: inputEl.value.trim()
        });

        if (response.data.verified) {
            captchaTokens[type] = true;
            showStatus(`${type}-captcha-status`, '✓ Captcha verified', 'success');
            inputEl.disabled = true;
        }
    } catch (error) {
        captchaTokens[type] = null;
        showStatus(`${type}-captcha-status`, error.response?.data?.error || 'Captcha error', 'error');
    }
}

async function checkUsernameAvailability(username) {
    try {
        const response = await axios.post(`${API_URL}/check-username`, { username });
        const statusEl = document.getElementById('username-check');

        if (response.data.available) {
            statusEl.textContent = '✓ Available';
            statusEl.style.color = '#28a745';
        } else {
            statusEl.textContent = '✗ Username already taken';
            statusEl.style.color = '#dc3545';
        }
    } catch (error) {
        console.error('Username check error:', error);
    }
}

async function handleLogin(e) {
    e.preventDefault();

    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    const captchaVerified = !!captchaTokens.login;

    if (!username || !password) {
        showStatus('loginStatus', 'Username and password required', 'error');
        return false;
    }

    if (!captchaVerified) {
        showStatus('loginStatus', 'Please complete captcha verification', 'error');
        return false;
    }

    const btn = document.getElementById('login-button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Logging in...';

    try {
        const response = await axios.post(`${API_URL}/login`, { username, password });

        localStorage.setItem('authToken', response.data.token);

        document.getElementById('profileUsername').textContent = response.data.user.username;
        document.getElementById('profileId').textContent = response.data.user.id;
        document.getElementById('profileRobloxId').textContent = response.data.user.roblox_id || 'N/A';

        document.getElementById('userProfile').classList.add('show');
        showStatus('loginStatus', `Welcome ${username}! ✓`, 'success');

        setTimeout(() => {
            document.getElementById('login').style.display = 'none';
        }, 2000);
    } catch (error) {
        showStatus('loginStatus', error.response?.data?.error || 'Login failed', 'error');
        loadCaptchaChallenge('login');
        document.getElementById('login-captcha-answer').disabled = false;
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Log In';
    }

    return false;
}

async function handleRegister(e) {
    e.preventDefault();

    const username = document.getElementById('register-username').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    const passwordConfirm = document.getElementById('register-password-confirm').value;
    const captchaVerified = !!captchaTokens.register;

    if (!username || !email || !password) {
        showStatus('registerStatus', 'All fields required', 'error');
        return false;
    }

    if (password !== passwordConfirm) {
        showStatus('registerStatus', 'Passwords do not match', 'error');
        return false;
    }

    if (password.length < 8) {
        showStatus('registerStatus', 'Password must be at least 8 characters', 'error');
        return false;
    }

    if (!captchaVerified) {
        showStatus('registerStatus', 'Please complete captcha verification', 'error');
        return false;
    }

    const btn = document.getElementById('register-button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Creating Account...';

    try {
        await axios.post(`${API_URL}/register`, { username, email, password });

        showStatus('registerStatus', 'Account created! Please login.', 'success');

        setTimeout(() => {
            document.getElementById('register-username').value = '';
            document.getElementById('register-email').value = '';
            document.getElementById('register-password').value = '';
            document.getElementById('register-password-confirm').value = '';
            captchaTokens.register = null;
            document.getElementById('register-captcha-answer').disabled = false;
            loadCaptchaChallenge('register');
            switchTab('login');
        }, 1500);
    } catch (error) {
        showStatus('registerStatus', error.response?.data?.error || 'Registration failed', 'error');
        loadCaptchaChallenge('register');
        document.getElementById('register-captcha-answer').disabled = false;
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Create Account';
    }

    return false;
}

function switchTab(tabName) {
    document.querySelectorAll('.form-section').forEach((s) => s.classList.remove('active'));
    document.querySelectorAll('.form-tab-btn').forEach((b) => b.classList.remove('active'));

    document.getElementById(tabName).classList.add('active');

    document.querySelectorAll('.form-tab-btn').forEach((btn) => {
        if (btn.textContent.toLowerCase().includes(tabName === 'login' ? 'login' : 'sign')) {
            btn.classList.add('active');
        }
    });
}

function showStatus(elementId, message, type) {
    const element = document.getElementById(elementId);
    if (!element) return;

    element.className = `status-msg show status-${type}`;
    element.textContent = message;

    if (type !== 'info') {
        setTimeout(() => {
            element.classList.remove('show');
        }, 5000);
    }
}

window.handleLogin = handleLogin;
window.handleRegister = handleRegister;
window.switchTab = switchTab;
