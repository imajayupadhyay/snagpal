<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('PUBLIC_PATH', ROOT_PATH . '/public');

require APP_PATH . '/support/env.php';

load_env_file(ROOT_PATH . '/.env');

$config = require APP_PATH . '/config/app.php';
$config['database'] = require APP_PATH . '/config/database.php';
$config['mail'] = require APP_PATH . '/config/mail.php';
$GLOBALS['config'] = $config;

date_default_timezone_set($config['timezone'] ?? 'UTC');

require APP_PATH . '/helpers.php';
require APP_PATH . '/database.php';
require APP_PATH . '/mailer.php';
require APP_PATH . '/homepage.php';
require APP_PATH . '/cohorts.php';
require APP_PATH . '/events.php';
require APP_PATH . '/scheduling.php';

$site = homepage_content();
