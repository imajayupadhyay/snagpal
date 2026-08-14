<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
    || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

function event_registration_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

$refererPath = event_registration_safe_referer_path();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($wantsJson) {
        event_registration_json_response([
            'ok' => false,
            'errors' => ['Invalid registration request.'],
        ], 405);
    }

    redirect($refererPath);
}

$result = event_registration_submit($_POST);

if ($result['ok'] === true) {
    if ($wantsJson) {
        event_registration_json_response([
            'ok' => true,
            'message' => (string) $result['message'],
        ]);
    }

    public_flash('event_registration', [
        'type' => 'success',
        'messages' => [(string) $result['message']],
        'old' => [],
    ]);
    redirect($refererPath . '?event_registration=success#upcoming-events');
}

$errors = $result['errors'] ?? ['Unable to submit your registration.'];

if ($wantsJson) {
    event_registration_json_response([
        'ok' => false,
        'errors' => $errors,
        'old' => $result['old'] ?? [],
    ], 422);
}

public_flash('event_registration', [
    'type' => 'error',
    'messages' => $errors,
    'old' => $result['old'] ?? [],
]);

redirect($refererPath . '?event_registration=error#upcoming-events');
