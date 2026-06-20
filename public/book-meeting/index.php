<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
    || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

function booking_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($wantsJson) {
        booking_json_response([
            'ok' => false,
            'errors' => ['Invalid booking request.'],
        ], 405);
    }

    redirect(url_path());
}

$result = schedule_submit_booking($_POST);

if ($result['ok'] === true) {
    if ($wantsJson) {
        booking_json_response([
            'ok' => true,
            'message' => (string) $result['message'],
            'slots' => schedule_available_slots(),
        ]);
    }

    public_flash('meeting_booking', [
        'type' => 'success',
        'messages' => [(string) $result['message']],
        'old' => [],
    ]);
    redirect(url_path() . '?booking=success#schedule');
}

$errors = $result['errors'] ?? ['Unable to book the selected slot.'];

if ($wantsJson) {
    booking_json_response([
        'ok' => false,
        'errors' => $errors,
        'old' => $result['old'] ?? [],
        'slots' => schedule_available_slots(),
    ], 422);
}

public_flash('meeting_booking', [
    'type' => 'error',
    'messages' => $errors,
    'old' => $result['old'] ?? [],
]);

redirect(url_path() . '?booking=error#schedule');
