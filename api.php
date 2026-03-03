<?php
require_once 'config.php';
$m = $_SERVER['REQUEST_METHOD'];
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$p = str_replace('/api/', '', $p);
$i = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($p) {
    case 'register': if ($m === 'POST') handleRegister($i); break;
    case 'login': if ($m === 'POST') handleLogin($i); break;
    case 'verify-captcha': if ($m === 'POST') handleVerifyCaptcha($i); break;
    case 'check-username': if ($m === 'POST') handleCheckUsername($i); break;
    case 'verify': if ($m === 'GET') handleVerifyToken(); break;
    case 'health': success(['status' => 'ok']); break;
    default: error('Endpoint not found', 404);
}

function handleRegister($i) { global $db; $u = sanitize_input($i['username'] ?? ''); $e = sanitize_input($i['email'] ?? ''); $p = $i['password'] ?? ''; if (!$u || !$e || !$p) error('All fields required'); if (!isset($_SESSION['captcha_verified'])) error('Captcha verification required'); if (strlen($p) < 8) error('Password must be 8+ characters'); if ($db->usernameExists($u)) error('Username already exists'); if ($db->createUser($u, $e, $p)) { log_action('User Registered', $u); success(['message' => 'Account created']); } error('Registration failed', 500); }

function handleLogin($i) { global $db; $u = sanitize_input($i['username'] ?? ''); $p = $i['password'] ?? ''; if (!$u || !$p) error('Username and password required'); if (!isset($_SESSION['captcha_verified'])) error('Captcha verification required'); $us = $db->getUserByUsername($u); if (!$us) error('Invalid credentials', 401); if (!verify_password($p, $us['password'])) error('Invalid credentials', 401); $ru = get_roblox_user($u); if (!$ru) error('Invalid credentials', 401); $t = create_jwt(['username' => $us['username'], 'id' => $us['id'], 'roblox_id' => $ru['id'] ?? null]); $_SESSION['user'] = ['username' => $us['username'], 'id' => $us['id'], 'roblox_id' => $ru['id'] ?? null]; log_action('User Login', $u); success(['token' => $t, 'user' => ['username' => $us['username'], 'email' => $us['email'], 'id' => $us['id'], 'roblox_id' => $ru['id'] ?? null]]); }

function handleVerifyCaptcha($i) { $t = $i['captchaToken'] ?? ''; if (!$t) error('Invalid captcha token'); $v = false; foreach (CDS_KEYS as $k) { if (strpos($t, $k) !== false) { $v = true; break; } } if ($v || strpos($t, 'cds_') === 0) { $_SESSION['captcha_verified'] = true; success(['session_id' => bin2hex(random_bytes(16))]); } error('Captcha verification failed'); }

function handleCheckUsername($i) { $u = sanitize_input($i['username'] ?? ''); if (!$u || strlen($u) < 3) error('Username must be 3+ characters'); $ru = get_roblox_user($u); if ($ru && isset($ru['id'])) success(['available' => false, 'roblox_id' => $ru['id']]); success(['available' => true]); }

function handleVerifyToken() { $a = $_SERVER['HTTP_AUTHORIZATION'] ?? ''; if (!$a) error('No token provided', 401); $t = str_replace('Bearer ', '', $a); $d = verify_jwt($t); if (!$d) error('Invalid token', 401); success(['user' => $d]); }
?>
