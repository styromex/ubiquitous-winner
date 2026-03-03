const API_URL = '/api';
let captchaTokens = {};

document.addEventListener('DOMContentLoaded', () => {
    setupCaptchaListeners();
    checkAuthStatus();
});

function setupCaptchaListeners() {
    const loginCheckbox = document.getElementById('login-captcha');
    const registerCheckbox = document.getElementById('register-captcha');
    const registerUsername = document.getElementById('register-username');

    if (loginCheckbox) {
        loginCheckbox.addEventListener('change', async (e) => {
            if (e.target.checked) await verifyCDSCaptcha('login');
        });
    }

    if (registerCheckbox) {
        registerCheckbox.addEventListener('change', async (e) => {
            if (e.target.checked) await verifyCDSCaptcha('register');
        });
    }

    if (registerUsername) {
        registerUsername.addEventListener('blur', async (e) => {
            const username = e.target.value;
            if (username.length >= 3) await checkUsernameAvailability(username);
        });
    }
}

async function checkAuthStatus() {
    const token = localStorage.getItem('authToken');
    if (token) {
        try {
            const response = await axios.get(`${API_URL}/verify`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.data.valid) {
                document.getElementById('login-form').style.display = 'none';
            }
        } catch (error) {
            localStorage.removeItem('authToken');
        }
    }
}

async function verifyCDSCaptcha(type) {
    try {
        showStatus(`${type}-captcha-status`, 'Verifying...', 'info');

        const cdsKeys = [
            'A2A14B1D-1AF3-C791-9BBC-EE33CC7A0A6F',
            '476068BF-9607-4799-B53D-966BE98E2B81',
            '63E4117F-E727-42B4-6DAA-C8448E9B137F',
            'CC30DB96-0C88-4DEB-86E5-6601927ACBB4'
        ];

        const randomKey = cdsKeys[Math.floor(Math.random() * cdsKeys.length)];
        const token = `cds_${randomKey}_${Date.now()}`;

        const response = await axios.post(`${API_URL}/verify-captcha`, {
            captchaToken: token
        });

        if (response.data.success) {
            captchaTokens[type] = token;
            showStatus(`${type}-captcha-status`, '✓ Captcha verified', 'success');
        } else {
            showStatus(`${type}-captcha-status`, 'Verification failed', 'error');
            document.getElementById(`${type}-captcha`).checked = false;
        }
    } catch (error) {
        showStatus(`${type}-captcha-status`, error.response?.data?.error || 'Captcha error', 'error');
        document.getElementById(`${type}-captcha`).checked = false;
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
            statusEl.textContent = '✗ Username taken on Roblox';
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
    const captchaVerified = !!captchaTokens['login'];

    if (!username || !password) {
        showStatus('loginStatus', 'Username and password required', 'error');
        return false;
    }

    if (!captchaVerified) {
        showStatus('loginStatus', 'Please verify captcha', 'error');
        return false;
    }

    const btn = document.getElementById('login-button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Logging in...';

    try {
        const response = await axios.post(`${API_URL}/login`, {
            username,
            password
        });

        localStorage.setItem('authToken', response.data.token);

        document.getElementById('profileUsername').textContent = response.data.user.username;
        document.getElementById('profileId').textContent = response.data.user.id;
        document.getElementById('profileRobloxId').textContent = response.data.user.roblox_id || 'N/A';

        document.getElementById('userProfile').classList.add('show');
        showStatus('loginStatus', `Welcome ${username}! ✓`, 'success');

        setTimeout(() => {
            document.getElementById('login-form').style.display = 'none';
        }, 2000);
    } catch (error) {
        showStatus('loginStatus', error.response?.data?.error || 'Login failed', 'error');
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
    const captchaVerified = !!captchaTokens['register'];

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
        showStatus('registerStatus', 'Please verify captcha', 'error');
        return false;
    }

    const btn = document.getElementById('register-button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Creating Account...';

    try {
        const response = await axios.post(`${API_URL}/register`, {
            username,
            email,
            password
        });

        showStatus('registerStatus', 'Account created! Please login.', 'success');

        setTimeout(() => {
            document.getElementById('register-username').value = '';
            document.getElementById('register-email').value = '';
            document.getElementById('register-password').value = '';
            document.getElementById('register-password-confirm').value = '';
            document.getElementById('register-captcha').checked = false;
            captchaTokens['register'] = null;
            switchTab('login');
        }, 2000);
    } catch (error) {
        showStatus('registerStatus', error.response?.data?.error || 'Registration failed', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Create Account';
    }

    return false;
}

function switchTab(tabName) {
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.form-tab-btn').forEach(b => b.classList.remove('active'));

    document.getElementById(tabName).classList.add('active');

    document.querySelectorAll('.form-tab-btn').forEach(btn => {
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