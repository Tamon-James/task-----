<?php
declare(strict_types=1);

$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    exit('config.php がありません。config.example.php をコピーして設定してください。');
}

require_once $configPath;

const CANONICAL_HOST = 'task.james-tamon.com';
const CANONICAL_SCHEME = 'https';

function currentPath(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($path) || $path === '' || $path === '/') {
        return 'index.php';
    }

    return basename($path);
}

function isHttpsRequest(): bool
{
    $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $forwardedSsl = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));

    return $https === 'on'
        || $https === '1'
        || $forwardedProto === 'https'
        || $forwardedSsl === 'on'
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

function enforceCanonicalUrl(): void
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return;
    }

    $host = preg_replace('/:\d+$/', '', $host) ?? $host;
    $scheme = isHttpsRequest() ? 'https' : 'http';

    if ($host === CANONICAL_HOST && $scheme === CANONICAL_SCHEME) {
        return;
    }

    if (!in_array($host, [CANONICAL_HOST, 'www.' . CANONICAL_HOST], true)) {
        return;
    }

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: ' . CANONICAL_SCHEME . '://' . CANONICAL_HOST . $requestUri, true, 301);
    exit;
}

enforceCanonicalUrl();

$secureCookie = isHttpsRequest();
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['logged_in']);
}

function requireLogin(): void
{
    if (isLoggedIn()) {
        return;
    }

    $current = currentPath();
    if (in_array($current, ['login.php', 'logout.php'], true)) {
        return;
    }

    $next = $current;
    header('Location: login.php?next=' . urlencode($next));
    exit;
}

function loginUser(): void
{
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrfToken(): void
{
    $postedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($postedToken) || !hash_equals(csrfToken(), $postedToken)) {
        http_response_code(400);
        exit('不正なリクエストです。画面を再読み込みしてから再度お試しください。');
    }
}
