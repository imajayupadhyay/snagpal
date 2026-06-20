<?php

declare(strict_types=1);

return [
    // SMTP transport (Hostinger): ssl on 465, or tls (STARTTLS) on 587.
    'host' => (string) env_value('MAIL_HOST', 'smtp.hostinger.com'),
    'port' => (int) env_value('MAIL_PORT', 465),
    'encryption' => strtolower((string) env_value('MAIL_ENCRYPTION', 'ssl')),
    'username' => (string) env_value('MAIL_USERNAME', ''),
    'password' => (string) env_value('MAIL_PASSWORD', ''),
    'timeout' => (int) env_value('MAIL_TIMEOUT', 15),

    // Identity used on outgoing mail.
    'from_address' => (string) env_value('MAIL_FROM_ADDRESS', 'connect@shwetanagpal.com'),
    'from_name' => (string) env_value('MAIL_FROM_NAME', 'Shweta Nagpal'),

    // Where new-booking notifications are delivered.
    'admin_address' => (string) env_value('MAIL_ADMIN_ADDRESS', 'mshwetan@gmail.com'),

    // Master switch — when false (or credentials missing) the app silently skips sending.
    'enabled' => (bool) env_value('MAIL_ENABLED', true),
];
