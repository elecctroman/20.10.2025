<?php
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $baseDir = __DIR__ . '/';
    $relative = substr($class, strlen($prefix));
    $segments = explode('\\', $relative);
    if (!$segments) {
        return;
    }

    $segments[0] = strtolower($segments[0]);
    $file = $baseDir . implode('/', $segments) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/core/Helpers.php';

session_name(config('app.session_name'));
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set(config('app.timezone'));
