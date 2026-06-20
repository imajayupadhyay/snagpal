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
    return url_path('sanchalak/');
}

function admin_dashboard_url(): string
{
    return url_path('sanchalak/dashboard/');
}

function admin_homepage_url(): string
{
    return url_path('sanchalak/homepage/');
}

function admin_schedule_url(): string
{
    return url_path('sanchalak/schedule/');
}

function admin_bookings_url(): string
{
    return url_path('sanchalak/bookings/');
}

function admin_users_url(): string
{
    return url_path('sanchalak/users/');
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

/* ============================================================
   Admin user management
   ============================================================ */

function admin_users_all(): array
{
    return db()->query(
        'SELECT id, name, email, is_active, last_login_at, created_at
         FROM admin_users
         ORDER BY created_at ASC, id ASC'
    )->fetchAll();
}

function admin_users_active_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM admin_users WHERE is_active = 1')->fetchColumn();
}

/**
 * Validate and create a new admin. Returns a list of errors (empty on success).
 */
function admin_create_user(array $post): array
{
    $name = trim((string) ($post['name'] ?? ''));
    $email = strtolower(trim((string) ($post['email'] ?? '')));
    $password = (string) ($post['password'] ?? '');
    $confirm = (string) ($post['password_confirm'] ?? '');

    $errors = admin_validate_identity($name, $email);
    $errors = array_merge($errors, admin_validate_password($password, $confirm));

    if ($errors !== []) {
        return $errors;
    }

    $exists = db()->prepare('SELECT id FROM admin_users WHERE email = :email LIMIT 1');
    $exists->execute(['email' => $email]);

    if ($exists->fetch()) {
        return ['An admin with that email already exists.'];
    }

    db()->prepare(
        'INSERT INTO admin_users (name, email, password_hash, is_active)
         VALUES (:name, :email, :hash, 1)'
    )->execute([
        'name' => $name,
        'email' => $email,
        'hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    return [];
}

/**
 * Validate and update an admin's password. Returns a list of errors (empty on success).
 */
function admin_update_password(int $id, array $post): array
{
    if ($id <= 0) {
        return ['Invalid admin selected.'];
    }

    $password = (string) ($post['password'] ?? '');
    $confirm = (string) ($post['password_confirm'] ?? '');

    $errors = admin_validate_password($password, $confirm);

    if ($errors !== []) {
        return $errors;
    }

    $exists = db()->prepare('SELECT id FROM admin_users WHERE id = :id LIMIT 1');
    $exists->execute(['id' => $id]);

    if (! $exists->fetch()) {
        return ['That admin no longer exists.'];
    }

    db()->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id')->execute([
        'hash' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $id,
    ]);

    return [];
}

/**
 * Delete an admin. Guards against removing yourself or the last admin.
 */
function admin_delete_user(int $id, int $currentAdminId): array
{
    if ($id <= 0) {
        return ['Invalid admin selected.'];
    }

    if ($id === $currentAdminId) {
        return ['You cannot delete the admin you are signed in as.'];
    }

    if (admin_users_active_count() <= 1) {
        return ['At least one admin must remain.'];
    }

    $statement = db()->prepare('DELETE FROM admin_users WHERE id = :id');
    $statement->execute(['id' => $id]);

    if ($statement->rowCount() === 0) {
        return ['That admin no longer exists.'];
    }

    return [];
}

function admin_validate_identity(string $name, string $email): array
{
    $errors = [];

    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        $errors[] = 'Enter a name between 2 and 120 characters.';
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $errors[] = 'Enter a valid email address.';
    }

    return $errors;
}

function admin_validate_password(string $password, string $confirm): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'The password and confirmation do not match.';
    }

    return $errors;
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
