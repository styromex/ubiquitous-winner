#!/bin/bash

echo "========================================"
echo "Roblox Login System - Setup Helper"
echo "========================================"
echo ""

read -p "Enter your DirectAdmin username: " USERNAME

PATHS=(
    "/home/$USERNAME/public_html"
    "/var/www/$USERNAME"
    "/var/www/html/$USERNAME"
    "/home/$USERNAME/www"
)

FOUND_PATH=""

for path in "${PATHS[@]}"; do
    if [ -d "$path" ]; then
        echo "✅ Found: $path"
        FOUND_PATH="$path"
        break
    fi
done

if [ -z "$FOUND_PATH" ]; then
    echo "❌ Could not find directory"
    echo "Try: cd /home/$USERNAME/public_html"
    echo "Or ask your host for the document root"
    exit 1
fi

echo "Installing to: $FOUND_PATH"

mkdir -p "$FOUND_PATH/data" "$FOUND_PATH/logs"

# Create config.php
cat > "$FOUND_PATH/config.php" << 'CFGEOF'
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'roblox_login');
define('SESSION_NAME', 'roblox_login');
define('SESSION_LIFETIME', 86400);
define('JWT_SECRET', bin2hex(random_bytes(32)));
define('CDS_KEYS', ['A2A14B1D-1AF3-C791-9BBC-EE33CC7A0A6F', '476068BF-9607-4799-B53D-966BE98E2B81', '63E4117F-E727-42B4-6DAA-C8448E9B137F', 'CC30DB96-0C88-4DEB-86E5-6601927ACBB4']);
define('ROBLOX_API_URL', 'https://api.roblox.com/users/get-by-username');

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
session_name(SESSION_NAME);
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(0); }

function response($data, $status = 200) { http_response_code($status); echo json_encode($data); exit; }
function error($msg, $s = 400) { response(['error' => $msg], $s); }
function success($d = []) { response(array_merge(['success' => true], $d), 200); }
function log_action($a, $d = '') { $ld = __DIR__ . '/logs'; @mkdir($ld, 0755, true); $ts = date('Y-m-d H:i:s'); $m = "[$ts] $a" . ($d ? ": $d" : "") . "\n"; @file_put_contents("$ld/activity.log", $m, FILE_APPEND); }
function sanitize_input($i) { return htmlspecialchars(trim($i), ENT_QUOTES, 'UTF-8'); }
function get_roblox_user($u) { try { $url = ROBLOX_API_URL . '?username=' . urlencode($u); $ch = curl_init(); curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_USERAGENT => 'RobloxLogin/1.0']); $r = curl_exec($ch); curl_close($ch); return json_decode($r, true); } catch (Exception $e) { return null; } }
function hash_password($p) { return password_hash($p, PASSWORD_BCRYPT, ['cost' => 10]); }
function verify_password($p, $h) { return password_verify($p, $h); }
function create_jwt($d) { $h = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])); $p = base64_encode(json_encode(array_merge($d, ['iat' => time(), 'exp' => time() + 86400]))); $s = base64_encode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true)); return "$h.$p.$s"; }
function verify_jwt($t) { try { list($h, $p, $s) = explode('.', $t); $es = base64_encode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true)); if (!hash_equals($s, $es)) return null; $d = json_decode(base64_decode($p), true); if ($d['exp'] < time()) return null; return $d; } catch (Exception $e) { return null; } }

class Database {
    public function createUser($u, $e, $p, $r = null) { $f = __DIR__ . '/data/users.json'; $us = file_exists($f) ? json_decode(file_get_contents($f), true) : []; if (isset($us[$u])) return false; $us[$u] = ['id' => bin2hex(random_bytes(8)), 'username' => $u, 'email' => $e, 'password' => hash_password($p), 'roblox_id' => $r, 'created_at' => date('Y-m-d H:i:s')]; @mkdir(__DIR__ . '/data', 0755, true); file_put_contents($f, json_encode($us, JSON_PRETTY_PRINT)); return true; }
    public function getUserByUsername($u) { $f = __DIR__ . '/data/users.json'; if (!file_exists($f)) return null; $us = json_decode(file_get_contents($f), true); return $us[$u] ?? null; }
    public function usernameExists($u) { return $this->getUserByUsername($u) !== null; }
}

$db = new Database();
?>
CFGEOF

echo "✅ Created config.php"

# Create api.php
cat > "$FOUND_PATH/api.php" << 'APIEOF'
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
APIEOF

echo "✅ Created api.php"

# Create .htaccess
cat > "$FOUND_PATH/.htaccess" << 'HTEOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/(.*)$ api.php?path=$1 [QSA,L]
</IfModule>
HTEOF

echo "✅ Created .htaccess"

chmod 755 "$FOUND_PATH" "$FOUND_PATH/data" "$FOUND_PATH/logs"
chmod 644 "$FOUND_PATH"/*.php "$FOUND_PATH"/.htaccess

echo ""
echo "========================================"
echo "✅ Complete!"
echo "========================================"
echo ""
echo "Path: $FOUND_PATH"
echo ""
echo "Next: Upload index.html and auth.js"
echo "Test: curl http://your-domain.com/api/health"
echo ""
