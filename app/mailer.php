<?php

declare(strict_types=1);

/**
 * Dependency-free SMTP mailer (Hostinger-ready) plus the booking
 * notification templates. Sending never throws: failures are logged so the
 * booking flow keeps working even when mail is misconfigured or unreachable.
 */

function mail_config(): array
{
    return $GLOBALS['config']['mail'] ?? [];
}

function mailer_log(string $message): void
{
    error_log('[mailer] ' . $message);
}

/**
 * Send an HTML email via SMTP. Returns true on success, false otherwise.
 *
 * @param array{reply_to?:string,reply_name?:string} $options
 */
function send_mail(string $toEmail, string $toName, string $subject, string $html, string $text = '', array $options = []): bool
{
    $cfg = mail_config();

    if (empty($cfg['enabled'])) {
        mailer_log('skipped (MAIL_ENABLED is false): ' . $subject);
        return false;
    }

    if (empty($cfg['username']) || empty($cfg['password'])) {
        mailer_log('skipped (SMTP credentials not set in .env): ' . $subject);
        return false;
    }

    if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        mailer_log('skipped (invalid recipient "' . $toEmail . '")');
        return false;
    }

    try {
        return (new SmtpMailer($cfg))->send($toEmail, $toName, $subject, $html, $text, $options);
    } catch (Throwable $e) {
        mailer_log('send failed for "' . $toEmail . '": ' . $e->getMessage());
        return false;
    }
}

final class SmtpMailer
{
    /** @var resource|null */
    private $conn = null;

    public function __construct(private array $cfg)
    {
    }

    public function send(string $toEmail, string $toName, string $subject, string $html, string $text, array $options = []): bool
    {
        $host = (string) ($this->cfg['host'] ?? '');
        $port = (int) ($this->cfg['port'] ?? 465);
        $enc = strtolower((string) ($this->cfg['encryption'] ?? 'ssl'));
        $timeout = max(5, (int) ($this->cfg['timeout'] ?? 15));
        $username = (string) ($this->cfg['username'] ?? '');
        $password = (string) ($this->cfg['password'] ?? '');
        $fromEmail = (string) ($this->cfg['from_address'] ?? $username);
        $fromName = (string) ($this->cfg['from_name'] ?? '');

        $transport = ($enc === 'ssl' || $enc === 'smtps') ? 'ssl://' : 'tcp://';
        $context = stream_context_create([
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true],
        ]);

        $errno = 0;
        $errstr = '';
        $this->conn = @stream_socket_client(
            $transport . $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (! $this->conn) {
            throw new RuntimeException(sprintf('connect to %s:%d failed: %s (%d)', $host, $port, $errstr, $errno));
        }

        stream_set_timeout($this->conn, $timeout);

        try {
            $this->expect(220);
            $ehloName = $this->ehloName();
            $this->command('EHLO ' . $ehloName, 250);

            if ($enc === 'tls' || $enc === 'starttls') {
                $this->command('STARTTLS', 220);
                $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

                if (! stream_socket_enable_crypto($this->conn, true, $crypto)) {
                    throw new RuntimeException('STARTTLS negotiation failed');
                }

                $this->command('EHLO ' . $ehloName, 250);
            }

            $this->command('AUTH LOGIN', 334);
            $this->command(base64_encode($username), 334);
            $this->command(base64_encode($password), 235);

            $this->command('MAIL FROM:<' . $fromEmail . '>', 250);
            $this->command('RCPT TO:<' . $toEmail . '>', 250);
            $this->command('DATA', 354);

            $message = $this->buildMessage($fromEmail, $fromName, $toEmail, $toName, $subject, $html, $text, $options);
            // Dot-stuffing per RFC 5321, then terminate with <CRLF>.<CRLF>.
            $message = preg_replace('/^\./m', '..', $message);
            $this->write($message . "\r\n.\r\n");
            $this->expect(250);

            // Be lenient on QUIT: the server may drop the connection.
            $this->write("QUIT\r\n");
        } finally {
            if (is_resource($this->conn)) {
                fclose($this->conn);
            }
            $this->conn = null;
        }

        return true;
    }

    private function ehloName(): string
    {
        $from = (string) ($this->cfg['from_address'] ?? '');
        $at = strrpos($from, '@');
        $domain = $at !== false ? substr($from, $at + 1) : '';

        return $domain !== '' ? $domain : 'localhost';
    }

    private function buildMessage(string $fromEmail, string $fromName, string $toEmail, string $toName, string $subject, string $html, string $text, array $options): string
    {
        if ($text === '') {
            $spaced = preg_replace('/<\/(p|div|h[1-6]|tr|li|br)>/i', "\n", $html) ?? $html;
            $text = trim(html_entity_decode(strip_tags($spaced), ENT_QUOTES, 'UTF-8'));
            $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        }

        $replyTo = (string) ($options['reply_to'] ?? $this->cfg['from_address'] ?? $fromEmail);
        $replyName = (string) ($options['reply_name'] ?? '');
        $boundary = 'bnd_' . bin2hex(random_bytes(12));

        $headers = [
            'Date: ' . date('r'),
            'From: ' . $this->addressHeader($fromName, $fromEmail),
            'To: ' . $this->addressHeader($toName, $toEmail),
            'Reply-To: ' . $this->addressHeader($replyName, $replyTo),
            'Subject: ' . $this->encodeHeader($subject),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $this->ehloName() . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $body = '--' . $boundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text)) . "\r\n"
            . '--' . $boundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html)) . "\r\n"
            . '--' . $boundary . "--\r\n";

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function addressHeader(string $name, string $email): string
    {
        $name = trim($name);

        if ($name === '') {
            return $email;
        }

        if (preg_match('/[^\x20-\x7E]/', $name)) {
            return $this->encodeHeader($name) . ' <' . $email . '>';
        }

        if (preg_match('/[",<>@:;\\\\]/', $name)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $name) . '" <' . $email . '>';
        }

        return $name . ' <' . $email . '>';
    }

    private function encodeHeader(string $value): string
    {
        if (preg_match('/[^\x20-\x7E]/', $value)) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }

        return $value;
    }

    private function command(string $command, int $expected): void
    {
        $this->write($command . "\r\n");
        $this->expect($expected);
    }

    private function write(string $data): void
    {
        if (! is_resource($this->conn) || fwrite($this->conn, $data) === false) {
            throw new RuntimeException('socket write failed');
        }
    }

    private function expect(int $code): string
    {
        $response = $this->readResponse();

        if ((int) substr($response, 0, 3) !== $code) {
            throw new RuntimeException('expected ' . $code . ', got: ' . trim($response));
        }

        return $response;
    }

    private function readResponse(): string
    {
        $data = '';

        while (is_resource($this->conn) && ($line = fgets($this->conn, 515)) !== false) {
            $data .= $line;

            // Last line of a (possibly multiline) reply has a space as the 4th char.
            if (! isset($line[3]) || $line[3] === ' ') {
                break;
            }

            $meta = stream_get_meta_data($this->conn);
            if (! empty($meta['timed_out'])) {
                throw new RuntimeException('read timed out');
            }
        }

        if ($data === '') {
            throw new RuntimeException('no response from server');
        }

        return $data;
    }
}

/* ============================================================
   Email templates
   ============================================================ */

function email_brand_name(): string
{
    $site = $GLOBALS['site'] ?? [];

    return (string) ($site['identity']['full_name'] ?? ($GLOBALS['config']['mail']['from_name'] ?? 'Shweta Nagpal'));
}

function email_layout(string $heading, string $statusLabel, string $statusColor, string $statusBg, string $introHtml, array $rows, string $footerHtml = ''): string
{
    $brand = e(email_brand_name());
    $rowsHtml = '';

    foreach ($rows as $label => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        $rowsHtml .= '<tr>'
            . '<td style="padding:9px 0;width:130px;color:#6C7470;font-size:13px;vertical-align:top;">' . e((string) $label) . '</td>'
            . '<td style="padding:9px 0;color:#16201D;font-size:14px;vertical-align:top;">' . nl2br(e((string) $value)) . '</td>'
            . '</tr>';
    }

    $statusPill = $statusLabel === '' ? '' :
        '<span style="display:inline-block;padding:5px 13px;border-radius:999px;font-size:12px;font-weight:700;'
        . 'text-transform:uppercase;letter-spacing:.06em;color:' . $statusColor . ';background:' . $statusBg . ';">'
        . e($statusLabel) . '</span>';

    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;padding:0;background:#F1EEE6;font-family:Arial,Helvetica,sans-serif;color:#16201D;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1EEE6;padding:28px 14px;">'
        . '<tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFFFF;border:1px solid rgba(20,30,27,.12);border-radius:14px;overflow:hidden;">'
        . '<tr><td style="background:#08322D;padding:22px 28px;">'
        . '<div style="font-family:\'Arial Narrow\',Arial,sans-serif;font-weight:bold;text-transform:uppercase;letter-spacing:.02em;color:#FFFFFF;font-size:19px;">' . $brand . '</div>'
        . '<div style="color:#9FB6B0;font-size:11px;letter-spacing:.14em;text-transform:uppercase;margin-top:3px;">Meetings</div>'
        . '</td></tr>'
        . '<tr><td style="padding:30px 28px 8px;">'
        . ($statusPill !== '' ? '<div style="margin-bottom:14px;">' . $statusPill . '</div>' : '')
        . '<h1 style="margin:0 0 12px;font-family:\'Arial Narrow\',Arial,sans-serif;text-transform:uppercase;font-size:24px;line-height:1.15;color:#16201D;">' . e($heading) . '</h1>'
        . '<div style="font-size:14px;line-height:1.6;color:#39433F;">' . $introHtml . '</div>'
        . '</td></tr>'
        . '<tr><td style="padding:10px 28px 6px;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid rgba(20,30,27,.1);border-bottom:1px solid rgba(20,30,27,.1);margin:6px 0;">' . $rowsHtml . '</table>'
        . '</td></tr>'
        . ($footerHtml !== '' ? '<tr><td style="padding:6px 28px 26px;font-size:13px;line-height:1.6;color:#39433F;">' . $footerHtml . '</td></tr>' : '<tr><td style="padding:0 0 14px;"></td></tr>')
        . '<tr><td style="background:#FAF8F2;padding:16px 28px;border-top:1px solid rgba(20,30,27,.08);color:#6C7470;font-size:11px;line-height:1.5;">'
        . 'This message was sent automatically by ' . $brand . '. Please do not reply if you did not request a meeting.'
        . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}

/**
 * Notify the visitor (pending) and the admin when a new request comes in.
 *
 * @param array{name:string,email:string,phone?:string,message?:string,date_label:string,time_label:string} $booking
 */
function notify_booking_requested(array $booking): void
{
    $brand = email_brand_name();
    $name = (string) $booking['name'];
    $email = (string) $booking['email'];
    $phone = (string) ($booking['phone'] ?? '');
    $message = (string) ($booking['message'] ?? '');
    $dateLabel = (string) $booking['date_label'];
    $timeLabel = (string) $booking['time_label'];

    // 1) Visitor — request received, pending confirmation.
    $visitorHtml = email_layout(
        'We received your request',
        'Pending confirmation',
        '#8A6A18',
        '#FBF0D9',
        '<p style="margin:0 0 10px;">Hi ' . e($name) . ', thanks for requesting a meeting with ' . e($brand) . '.</p>'
            . '<p style="margin:0;">Your request below is <strong>pending confirmation</strong>. You will receive another email as soon as it is confirmed.</p>',
        ['Date' => $dateLabel, 'Time' => $timeLabel, 'Your message' => $message]
    );
    send_mail($email, $name, 'We received your meeting request', $visitorHtml);

    // 2) Admin — new request, reply goes straight to the visitor.
    $cfg = mail_config();
    $adminEmail = (string) ($cfg['admin_address'] ?? '');

    if ($adminEmail !== '') {
        $adminUrl = (string) ($GLOBALS['config']['url'] ?? '') . url_path('sanchalak/bookings/');
        $adminHtml = email_layout(
            'New meeting request',
            'Action needed',
            '#284A73',
            '#E8EDF5',
            '<p style="margin:0 0 10px;"><strong>' . e($name) . '</strong> requested a meeting. Confirm or cancel it from the admin panel.</p>'
                . '<p style="margin:0;"><a href="' . e($adminUrl) . '" style="color:#0C5E55;font-weight:bold;">Open bookings &rarr;</a></p>',
            ['Date' => $dateLabel, 'Time' => $timeLabel, 'Name' => $name, 'Email' => $email, 'Phone' => $phone, 'Message' => $message],
            'Reply to this email to respond to ' . e($name) . ' directly.'
        );
        send_mail($adminEmail, $brand . ' (Admin)', 'New meeting request from ' . $name, $adminHtml, '', [
            'reply_to' => $email,
            'reply_name' => $name,
        ]);
    }
}

/**
 * Notify the visitor that their meeting is now confirmed.
 *
 * @param array{name:string,email:string,date_label:string,time_label:string} $booking
 */
function notify_booking_confirmed(array $booking): void
{
    $brand = email_brand_name();
    $name = (string) $booking['name'];
    $email = (string) $booking['email'];

    $html = email_layout(
        'Your meeting is confirmed',
        'Confirmed',
        '#08322D',
        '#E4EFEB',
        '<p style="margin:0 0 10px;">Hi ' . e($name) . ', good news — your meeting with ' . e($brand) . ' is now <strong>confirmed</strong>.</p>'
            . '<p style="margin:0;">Please keep the date and time below. If you need to make a change, just reply to this email.</p>',
        ['Date' => (string) $booking['date_label'], 'Time' => (string) $booking['time_label']]
    );

    send_mail($email, $name, 'Your meeting is confirmed', $html);
}
