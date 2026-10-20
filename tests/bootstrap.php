<?php declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

foreach (['App', 'Autoload', 'Database', 'Exceptions', 'Feature', 'Logger', 'Modules', 'Routing', 'Services', 'Validation', 'View'] as $config) {
    require_once __DIR__ . '/_support/app/Config/' . $config . '.php';
}

defined('CI_DEBUG') || define('CI_DEBUG', false);

require dirname(__DIR__) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';
