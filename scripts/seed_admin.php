<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$name = trim((string) env_value('ADMIN_SEED_NAME', 'admin'));
$email = strtolower(trim((string) env_value('ADMIN_SEED_EMAIL', 'admin@gmail.com')));
$password = (string) env_value('ADMIN_SEED_PASSWORD', 'Admin@123');

if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    fwrite(STDERR, "Invalid admin seed data. Check portfolio/.env.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $statement = db()->prepare(
        'INSERT INTO admin_users (name, email, password_hash, is_active)
         VALUES (:name, :email, :password_hash, 1)
         ON DUPLICATE KEY UPDATE
             name = VALUES(name),
             password_hash = VALUES(password_hash),
             is_active = 1,
             updated_at = CURRENT_TIMESTAMP'
    );

    $statement->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $hash,
    ]);

    echo "Seeded admin user: {$email}\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");

    if ($exception->getPrevious()) {
        fwrite(STDERR, $exception->getPrevious()->getMessage() . "\n");
    }

    exit(1);
}
