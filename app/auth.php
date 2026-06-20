<?php

declare(strict_types=1);

function start_admin_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $sessionPath = ROOT_PATH . '/storage/sessions';

    if (! is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }

    session_name('shweta_admin_session');
    session_save_path($sessionPath);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_admin_session();

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    start_admin_session();

    return is_string($token)
        && isset($_SESSION['_csrf_token'])
        && hash_equals((string) $_SESSION['_csrf_token'], $token);
}

function admin_login_url(): string
{
    return url_path('admin-secure-login/');
}

function admin_dashboard_url(): string
{
    return url_path('admin/');
}

function admin_homepage_url(): string
{
    return url_path('admin/homepage/');
}

function admin_schedule_url(): string
{
    return url_path('admin/schedule/');
}

function admin_bookings_url(): string
{
    return url_path('admin/bookings/');
}

function current_admin(): ?array
{
    start_admin_session();

    if (empty($_SESSION['admin_user_id'])) {
        return null;
    }

    static $admin = null;

    if (is_array($admin)) {
        return $admin;
    }

    $statement = db()->prepare('SELECT id, name, email, last_login_at, created_at FROM admin_users WHERE id = :id AND is_active = 1 LIMIT 1');
    $statement->execute(['id' => (int) $_SESSION['admin_user_id']]);
    $admin = $statement->fetch() ?: null;

    if ($admin === null) {
        unset($_SESSION['admin_user_id']);
    }

    return $admin;
}

function attempt_admin_login(string $email, string $password): bool
{
    start_admin_session();

    $email = strtolower(trim($email));

    $statement = db()->prepare('SELECT id, name, email, password_hash FROM admin_users WHERE email = :email AND is_active = 1 LIMIT 1');
    $statement->execute(['email' => $email]);
    $admin = $statement->fetch();

    if (! $admin || ! password_verify($password, (string) $admin['password_hash'])) {
        return false;
    }

    if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_DEFAULT)) {
        $rehash = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id')->execute([
            'hash' => $rehash,
            'id' => (int) $admin['id'],
        ]);
    }

    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = (int) $admin['id'];

    db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id')->execute([
        'id' => (int) $admin['id'],
    ]);

    return true;
}

function require_admin(): array
{
    $admin = current_admin();

    if ($admin === null) {
        redirect(admin_login_url());
    }

    return $admin;
}

function logout_admin(): void
{
    start_admin_session();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
