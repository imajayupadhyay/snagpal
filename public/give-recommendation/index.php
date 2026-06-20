<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
    || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

function recommendation_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

$refererPath = recommendation_safe_referer_path();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($wantsJson) {
        recommendation_json_response([
            'ok' => false,
            'errors' => ['Invalid request.'],
        ], 405);
    }

    redirect($refererPath);
}

$result = recommendation_submit($_POST);

if ($result['ok'] === true) {
    if ($wantsJson) {
        recommendation_json_response([
            'ok' => true,
            'message' => (string) $result['message'],
        ]);
    }

    public_flash('recommendation_submission', [
        'type' => 'success',
        'messages' => [(string) $result['message']],
        'old' => [],
    ]);
    redirect($refererPath . '?recommendation=success#recommendations');
}

$errors = $result['errors'] ?? ['Unable to submit your recommendation.'];

if ($wantsJson) {
    recommendation_json_response([
        'ok' => false,
        'errors' => $errors,
        'old' => $result['old'] ?? [],
    ], 422);
}

public_flash('recommendation_submission', [
    'type' => 'error',
    'messages' => $errors,
    'old' => $result['old'] ?? [],
]);

redirect($refererPath . '?recommendation=error#recommendations');
