<?php

declare(strict_types=1);

/**
 * Send a test email to verify the SMTP configuration in .env.
 *
 * Usage:
 *   php portfolio/scripts/test_mail.php you@example.com
 *   php portfolio/scripts/test_mail.php          (defaults to MAIL_ADMIN_ADDRESS)
 */

require dirname(__DIR__) . '/app/bootstrap.php';

$cfg = mail_config();
$to = $argv[1] ?? (string) ($cfg['admin_address'] ?? '');

echo "SMTP host : {$cfg['host']}:{$cfg['port']} ({$cfg['encryption']})\n";
echo "Username  : {$cfg['username']}\n";
echo "From      : {$cfg['from_name']} <{$cfg['from_address']}>\n";
echo "Enabled   : " . (! empty($cfg['enabled']) ? 'yes' : 'no') . "\n";
echo "Password  : " . (! empty($cfg['password']) ? 'set' : 'MISSING — fill MAIL_PASSWORD in .env') . "\n";

if ($to === '') {
    fwrite(STDERR, "No recipient. Pass one: php portfolio/scripts/test_mail.php you@example.com\n");
    exit(1);
}

echo "Sending test email to {$to} ...\n";

$ok = send_mail(
    $to,
    'Test Recipient',
    'SMTP test from ' . email_brand_name(),
    '<p>This is a <strong>test email</strong> confirming your Hostinger SMTP settings work.</p>'
        . '<p>If you can read this, booking notifications will send correctly.</p>'
);

echo $ok ? "OK — email sent. Check the inbox (and spam).\n" : "FAILED — see the [mailer] line in the error log above.\n";
exit($ok ? 0 : 1);
