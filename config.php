<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'roblox_login');
define('SESSION_NAME', 'roblox_login');
define('SESSION_LIFETIME', 86400);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 900);
define('CDS_KEYS', ['A2A14B1D-1AF3-C791-9BBC-EE33CC7A0A6F', '476068BF-9607-4799-B53D-966BE98E2B81', '63E4117F-E727-42B4-6DAA-C8448E9B137F', 'CC30DB96-0C88-4DEB-86E5-6601927ACBB4']);
define('ROBLOX_API_URL', 'https://api.roblox.com/users/get-by-username');

function get_jwt_secret()
{
    $envSecret = getenv('JWT_SECRET');
    if (is_string($envSecret) && trim($envSecret) !== '') {
        return trim($envSecret);
    }

    $secretFile = __DIR__ . '/data/.jwt_secret';
    if (file_exists($secretFile)) {
        $stored = trim((string) file_get_contents($secretFile));
        if ($stored !== '') {
            return $stored;
        }
    }

    @mkdir(__DIR__ . '/data', 0755, true);
    $generated = bin2hex(random_bytes(32));
    file_put_contents($secretFile, $generated);

    return $generated;
}

define('JWT_SECRET', get_jwt_secret());

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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

function response($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function error($msg, $s = 400)
{
    response(['error' => $msg], $s);
}

function success($d = [])
{
    response(array_merge(['success' => true], $d), 200);
}

function log_action($a, $d = '')
{
    $ld = __DIR__ . '/logs';
    @mkdir($ld, 0755, true);
    $ts = date('Y-m-d H:i:s');
    $m = "[$ts] $a" . ($d ? ": $d" : '') . "\n";
    @file_put_contents("$ld/activity.log", $m, FILE_APPEND);
}

function sanitize_input($i)
{
    return htmlspecialchars(trim($i), ENT_QUOTES, 'UTF-8');
}

function get_roblox_user($u)
{
    try {
        $url = ROBLOX_API_URL . '?username=' . urlencode($u);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'RobloxLogin/1.0'
        ]);
        $r = curl_exec($ch);
        curl_close($ch);

        return json_decode($r, true);
    } catch (Exception $e) {
        return null;
    }
}

function hash_password($p)
{
    return password_hash($p, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verify_password($p, $h)
{
    return password_verify($p, $h);
}

function needs_password_rehash($h)
{
    return password_needs_rehash($h, PASSWORD_BCRYPT, ['cost' => 10]);
}

function create_jwt($d)
{
    $h = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $p = base64_encode(json_encode(array_merge($d, ['iat' => time(), 'exp' => time() + 86400])));
    $s = base64_encode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));

    return "$h.$p.$s";
}

function verify_jwt($t)
{
    try {
        [$h, $p, $s] = explode('.', $t);
        $es = base64_encode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
        if (!hash_equals($s, $es)) return null;

        $d = json_decode(base64_decode($p), true);
        if ($d['exp'] < time()) return null;

        return $d;
    } catch (Exception $e) {
        return null;
    }
}

class Database
{
    private function getUsersFilePath()
    {
        return __DIR__ . '/data/users.json';
    }

    private function readUsers()
    {
        $f = $this->getUsersFilePath();
        if (!file_exists($f)) return [];

        return json_decode(file_get_contents($f), true) ?? [];
    }

    private function writeUsers($users)
    {
        @mkdir(__DIR__ . '/data', 0755, true);
        file_put_contents($this->getUsersFilePath(), json_encode($users, JSON_PRETTY_PRINT));
    }

    public function createUser($u, $e, $p, $r = null)
    {
        $us = $this->readUsers();
        if (isset($us[$u])) return false;

        $us[$u] = [
            'id' => bin2hex(random_bytes(8)),
            'username' => $u,
            'email' => $e,
            'password' => hash_password($p),
            'roblox_id' => $r,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->writeUsers($us);
        return true;
    }

    public function getUserByUsername($u)
    {
        $us = $this->readUsers();
        return $us[$u] ?? null;
    }

    public function usernameExists($u)
    {
        return $this->getUserByUsername($u) !== null;
    }

    public function updateUserPassword($u, $passwordHash)
    {
        $us = $this->readUsers();
        if (!isset($us[$u])) return false;

        $us[$u]['password'] = $passwordHash;
        $this->writeUsers($us);

        return true;
    }
}

$db = new Database();
?>
