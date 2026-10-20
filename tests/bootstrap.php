<?php declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'HistoryHeraldry\\SeedTracker\\';
    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
}, true, true);

foreach (['cache', 'logs', 'session'] as $directory) {
    $path = __DIR__ . '/_support/writable/' . $directory;
    if (! is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

foreach (['App', 'Autoload', 'Cache', 'Cookie', 'Database', 'Exceptions', 'Feature', 'Logger', 'Modules', 'Routing', 'Services', 'Session', 'Validation', 'View'] as $config) {
    require_once __DIR__ . '/_support/app/Config/' . $config . '.php';
}

defined('CI_DEBUG') || define('CI_DEBUG', false);

require dirname(__DIR__) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';
