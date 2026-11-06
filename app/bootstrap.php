<?php
/**
 * Loaded on every request: configuration, database connection, session,
 * security headers and the small helper functions used everywhere.
 */
declare(strict_types=1);

date_default_timezone_set('Asia/Colombo');

define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . '/public/uploads/articles');
define('PER_PAGE', 9);

/* ---------- Session ---------- */
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

/* ---------- Security headers ---------- */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; font-src 'self'; "
     . "img-src 'self' data: https://images.unsplash.com; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");

/* ---------- Database ---------- */
$configFile = ROOT_DIR . '/config.php';
$config = require (is_file($configFile) ? $configFile : ROOT_DIR . '/config.example.php');

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $db = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $db->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(503);
    exit('NewsDesk cannot reach its database. Check the values in config.php (see README.md).');
}

/** Thrown for any user-correctable problem; the message is shown on the page. */
class ValidationError extends RuntimeException {}

/* ---------- Database helpers (always prepared statements) ---------- */
function query(string $sql, array $params = []): PDOStatement
{
    global $db;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
function rows(string $sql, array $params = []): array   { return query($sql, $params)->fetchAll(); }
function one(string $sql, array $params = []): ?array   { return query($sql, $params)->fetch() ?: null; }
function scalar(string $sql, array $params = [])        { return query($sql, $params)->fetchColumn(); }
function last_id(): int                                 { global $db; return (int)$db->lastInsertId(); }

/* ---------- Request helpers ---------- */
/** Read a string from the query string; arrays and missing values become ''. */
function get_str(string $key, string $default = ''): string
{
    $v = $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}
function get_int(string $key, int $default = 0): int
{
    $v = $_GET[$key] ?? null;
    return (is_string($v) && ctype_digit($v)) ? (int)$v : $default;
}
/** Read a trimmed string from the posted form. */
function post(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
/** Value to put back into a form field: the posted value after a failed submit, otherwise the stored value. */
function old(string $key, $stored = ''): string
{
    return is_post() && isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : (string)$stored;
}

/* ---------- Output helpers ---------- */
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $page = 'home', array $args = []): string
{
    $args = array_filter($args, fn($v) => $v !== '' && $v !== null && $v !== 0);
    return 'index.php?' . http_build_query(['page' => $page] + $args);
}
function redirect(string $page = 'home', array $args = []): void
{
    header('Location: ' . url($page, $args));
    exit;
}
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function take_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

/** Render a template from /views with the given variables. */
function view(string $template, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require ROOT_DIR . '/views/' . $template . '.php';
}

/* ---------- Authentication ---------- */
function user(): ?array
{
    static $cached = false;
    if ($cached === false) {
        $cached = null;
        if (isset($_SESSION['user_id'])) {
            $cached = one("SELECT id, name, email, role, status, created_at FROM users WHERE id = ? AND status = 'active'", [$_SESSION['user_id']]);
            if (!$cached) unset($_SESSION['user_id']);   // deleted or blocked: end the session
        }
    }
    return $cached;
}
function user_id(): int { return (int)(user()['id'] ?? 0); }
function is_admin(): bool { return (user()['role'] ?? '') === 'admin'; }

/** Stop with a styled error page. */
function abort(int $code, string $title, string $message): void
{
    throw new HttpError($title . '|' . $message, $code);
}
class HttpError extends RuntimeException {}

function require_login(): void
{
    if (!user()) {
        flash('Sign in to continue.', 'info');
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('login');
    }
}
function require_admin(): void
{
    require_login();
    if (!is_admin()) abort(403, 'Administrators only', 'This area is for the NewsDesk editorial team. Your student pages are still available from the main site.');
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }

/* ---------- Validation ---------- */
function require_length(string $value, int $min, int $max, string $label): void
{
    $n = mb_strlen($value);
    if ($n < $min) throw new ValidationError($min === 1 ? "$label is required." : "$label needs at least $min characters.");
    if ($n > $max) throw new ValidationError("$label can be at most $max characters.");
}
function valid_date(string $value): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

require __DIR__ . '/helpers.php';
