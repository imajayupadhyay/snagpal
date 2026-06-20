<?php

declare(strict_types=1);

return [
    'name' => 'Shweta Nagpal Portfolio',
    'base_path' => '',
    'timezone' => 'Asia/Kolkata',
    'asset_version' => '1.0.12',
    // Absolute base URL, used to build links inside emails.
    'url' => rtrim((string) env_value('APP_URL', 'http://localhost:8000'), '/'),
];
