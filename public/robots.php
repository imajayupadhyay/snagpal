<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

echo seo_robots_txt(seo_settings());
