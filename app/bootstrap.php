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

$logPath = config('app.log_path');
$logDirectory = dirname($logPath);
if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0775, true);
}

ini_set('display_errors', config('app.debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', $logPath);

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

$exceptionHandler = function (Throwable $exception) {
    app_log('error', $exception->getMessage(), [
        'exception' => $exception,
    ]);

    $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'text/json')
        || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

    http_response_code(500);

    if (config('app.debug')) {
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $exception);
        } else {
            echo '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>';
        }
        return;
    }

    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'message' => 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.',
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    echo view('errors/500', [
        'message' => 'Beklenmeyen bir hata oluştu. Ekibimiz bilgilendirildi.',
    ], 'store', true);
};

set_exception_handler($exceptionHandler);

register_shutdown_function(function () use ($exceptionHandler) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $exceptionHandler(new ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        ));
    }
});
