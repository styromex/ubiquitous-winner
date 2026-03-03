<?php
require_once 'config.php';

$m = $_SERVER['REQUEST_METHOD'];
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$p = str_replace('/api/', '', $p);
$i = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($p) {
    case 'register':
        if ($m === 'POST') handleRegister($i);
        break;
    case 'login':
        if ($m === 'POST') handleLogin($i);
        break;
    case 'captcha-challenge':
        if ($m === 'GET') handleCaptchaChallenge();
        break;
    case 'verify-captcha':
        if ($m === 'POST') handleVerifyCaptcha($i);
        break;
    case 'check-username':
        if ($m === 'POST') handleCheckUsername($i);
        break;
    case 'verify':
        if ($m === 'GET') handleVerifyToken();
        break;
    case 'health':
        success(['status' => 'ok']);
        break;
    default:
        error('Endpoint not found', 404);
}

function handleRegister($i)
{
    global $db;

    $u = sanitize_input($i['username'] ?? '');
    $e = sanitize_input($i['email'] ?? '');
    $p = $i['password'] ?? '';

    if (!$u || !$e || !$p) error('All fields required');
    if (!isCaptchaVerified('register')) error('Captcha verification required');
    if (strlen($p) < 8) error('Password must be 8+ characters');
    if ($db->usernameExists($u)) error('Username already exists');

    if ($db->createUser($u, $e, $p)) {
        clearCaptchaState('register');
        log_action('User Registered', $u);
        success(['message' => 'Account created']);
    }

    error('Registration failed', 500);
}

function handleLogin($i)
{
    global $db;

    $u = sanitize_input($i['username'] ?? '');
    $p = $i['password'] ?? '';

    if (!$u || !$p) error('Username and password required');
    if (!isCaptchaVerified('login')) error('Captcha verification required');

    $throttleState = getLoginThrottleState($u);
    if ($throttleState['locked']) {
        error('Too many attempts. Please wait before trying again.', 429);
    }

    $us = $db->getUserByUsername($u);
    $validPassword = $us && verify_password($p, $us['password']);
    if (!$validPassword) {
        recordFailedLoginAttempt($u);
        error('Invalid credentials', 401);
    }

    resetFailedLoginAttempts($u);

    if (needs_password_rehash($us['password'])) {
        $db->updateUserPassword($u, hash_password($p));
    }

    $t = create_jwt([
        'username' => $us['username'],
        'id' => $us['id'],
        'roblox_id' => $us['roblox_id'] ?? null
    ]);

    $_SESSION['user'] = [
        'username' => $us['username'],
        'id' => $us['id'],
        'roblox_id' => $us['roblox_id'] ?? null
    ];

    clearCaptchaState('login');
    log_action('User Login', $u);

    success([
        'token' => $t,
        'user' => [
            'username' => $us['username'],
            'email' => $us['email'],
            'id' => $us['id'],
            'roblox_id' => $us['roblox_id'] ?? null
        ]
    ]);
}

function handleCaptchaChallenge()
{
    $form = $_GET['form'] ?? '';
    if (!in_array($form, ['login', 'register'], true)) {
        error('Invalid captcha form');
    }

    $left = random_int(1, 9);
    $right = random_int(1, 9);
    $answer = $left + $right;

    $_SESSION['captcha'][$form] = [
        'answer' => (string) $answer,
        'issued_at' => time()
    ];

    unset($_SESSION['captcha_verified'][$form]);

    success([
        'prompt' => "$left + $right",
        'expires_in' => 300
    ]);
}

function handleVerifyCaptcha($i)
{
    $form = $i['form'] ?? '';
    $answer = trim((string) ($i['answer'] ?? ''));

    if (!in_array($form, ['login', 'register'], true)) {
        error('Invalid captcha form');
    }

    if ($answer === '') {
        error('Captcha answer is required');
    }

    $challenge = $_SESSION['captcha'][$form] ?? null;
    if (!$challenge) {
        error('Captcha challenge missing. Please refresh captcha.');
    }

    if ((time() - (int) ($challenge['issued_at'] ?? 0)) > 300) {
        unset($_SESSION['captcha'][$form]);
        unset($_SESSION['captcha_verified'][$form]);
        error('Captcha expired. Please refresh captcha.');
    }

    if (!hash_equals((string) $challenge['answer'], $answer)) {
        error('Incorrect captcha answer');
    }

    $_SESSION['captcha_verified'][$form] = true;
    success(['verified' => true]);
}

function isCaptchaVerified($form)
{
    return isset($_SESSION['captcha_verified'][$form]) && $_SESSION['captcha_verified'][$form] === true;
}

function clearCaptchaState($form)
{
    unset($_SESSION['captcha'][$form]);
    unset($_SESSION['captcha_verified'][$form]);
}

function getLoginThrottleKey($username)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return hash('sha256', strtolower($username) . '|' . $ip);
}

function getLoginThrottleState($username)
{
    $key = getLoginThrottleKey($username);
    $now = time();

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    $entry = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'first_attempt_at' => $now];

    if (($now - (int) $entry['first_attempt_at']) > LOGIN_ATTEMPT_WINDOW) {
        $entry = ['count' => 0, 'first_attempt_at' => $now];
    }

    $_SESSION['login_attempts'][$key] = $entry;

    return [
        'count' => (int) $entry['count'],
        'locked' => ((int) $entry['count']) >= MAX_LOGIN_ATTEMPTS
    ];
}

function recordFailedLoginAttempt($username)
{
    $key = getLoginThrottleKey($username);
    $now = time();

    if (!isset($_SESSION['login_attempts'][$key])) {
        $_SESSION['login_attempts'][$key] = ['count' => 0, 'first_attempt_at' => $now];
    }

    if (($now - (int) $_SESSION['login_attempts'][$key]['first_attempt_at']) > LOGIN_ATTEMPT_WINDOW) {
        $_SESSION['login_attempts'][$key] = ['count' => 0, 'first_attempt_at' => $now];
    }

    $_SESSION['login_attempts'][$key]['count']++;
}

function resetFailedLoginAttempts($username)
{
    $key = getLoginThrottleKey($username);
    unset($_SESSION['login_attempts'][$key]);
}

function handleCheckUsername($i)
{
    global $db;

    $u = sanitize_input($i['username'] ?? '');
    if (!$u || strlen($u) < 3) error('Username must be 3+ characters');

    success(['available' => !$db->usernameExists($u)]);
}

function handleVerifyToken()
{
    $a = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$a) error('No token provided', 401);

    $t = str_replace('Bearer ', '', $a);
    $d = verify_jwt($t);
    if (!$d) error('Invalid token', 401);

    success(['valid' => true, 'user' => $d]);
}
?>
