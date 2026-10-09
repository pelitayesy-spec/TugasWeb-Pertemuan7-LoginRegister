<?php
declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

const USERS_FILE      = __DIR__ . '/../data/users.json';
const REMEMBER_COOKIE = 'remember_me';
const REMEMBER_DAYS   = 30;

/* ---------- Helper umum ---------- */

// Sanitasi output (cegah XSS)
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header("Location: $url");
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid (CSRF).');
    }
}

/* ---------- Penyimpanan JSON ---------- */

function load_users(): array
{
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $data = json_decode((string) file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}

function save_users(array $users): bool
{
    $dir = dirname(USERS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $json = json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

function find_user_by_email(string $email): ?array
{
    foreach (load_users() as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }
    return null;
}

function find_user_by_id(string $id): ?array
{
    foreach (load_users() as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

function update_user(string $id, array $changes): bool
{
    $users = load_users();
    foreach ($users as &$user) {
        if ($user['id'] === $id) {
            $user = array_merge($user, $changes);
            unset($user);
            return save_users($users);
        }
    }
    return false;
}

/* ---------- Auth & session ---------- */

function login_user(array $user): void
{
    session_regenerate_id(true); // cegah session fixation
    $_SESSION['user_id'] = $user['id'];
}

function is_logged_in(): bool
{
    if (isset($_SESSION['user_id']) && find_user_by_id($_SESSION['user_id'])) {
        return true;
    }
    return try_remember_login();
}

function current_user(): ?array
{
    return isset($_SESSION['user_id']) ? find_user_by_id($_SESSION['user_id']) : null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

/* ---------- Remember Me (bonus) ---------- */

function set_remember_cookie(string $userId): void
{
    $token   = bin2hex(random_bytes(32));
    $expires = time() + REMEMBER_DAYS * 86400;

    // Yang disimpan di JSON hanya hash token, bukan token aslinya
    update_user($userId, [
        'remember_token'   => hash('sha256', $token),
        'remember_expires' => $expires,
    ]);

    setcookie(REMEMBER_COOKIE, $userId . ':' . $token, [
        'expires'  => $expires,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function try_remember_login(): bool
{
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (!is_string($cookie) || !str_contains($cookie, ':')) {
        return false;
    }
    [$userId, $token] = explode(':', $cookie, 2);
    $user = find_user_by_id($userId);

    if (
        $user
        && !empty($user['remember_token'])
        && ($user['remember_expires'] ?? 0) > time()
        && hash_equals($user['remember_token'], hash('sha256', $token))
    ) {
        login_user($user);
        return true;
    }
    return false;
}

function clear_remember_cookie(?string $userId = null): void
{
    if ($userId) {
        update_user($userId, ['remember_token' => null, 'remember_expires' => null]);
    }
    setcookie(REMEMBER_COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
}
