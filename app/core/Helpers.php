<?php
use App\Core\Cache;
use App\Core\CSRF;

function config(string $key, $default = null)
{
    static $config = [];
    [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($config[$file])) {
        $path = __DIR__ . '/../config/' . $file . '.php';
        if (file_exists($path)) {
            $config[$file] = require $path;
        } else {
            $config[$file] = [];
        }
    }

    return $item ? ($config[$file][$item] ?? $default) : ($config[$file] ?? $default);
}

function database(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }

    $db = config('database');
    $dsn = sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s', $db['driver'], $db['host'], $db['port'], $db['database'], $db['charset']);

    $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);
    return $pdo;
}

function view(string $template, array $data = [], string $layout = 'store', bool $raw = false)
{
    extract($data);
    $lang = lang();
    $content = function () use ($template, $data, $lang) {
        extract($data);
        $path = __DIR__ . '/../views/' . $template . '.php';
        if (!file_exists($path)) {
            throw new RuntimeException('View not found: ' . $template);
        }
        include $path;
    };

    if ($raw) {
        ob_start();
        $content();
        return ob_get_clean();
    }

    $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';
    if (!file_exists($layoutPath)) {
        throw new RuntimeException('Layout not found: ' . $layout);
    }

    ob_start();
    include $layoutPath;
    return ob_get_clean();
}

function lang(?string $key = null)
{
    static $strings;
    if ($strings === null) {
        $locale = config('app.locale');
        $path = __DIR__ . '/../../lang/' . $locale . '.json';
        if (file_exists($path)) {
            $strings = json_decode(file_get_contents($path), true) ?: [];
        } else {
            $strings = [];
        }
    }

    if ($key === null) {
        return $strings;
    }

    return $strings[$key] ?? $key;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(CSRF::token(), ENT_QUOTES, 'UTF-8') . '">';
}

function cache(): Cache
{
    static $cache;
    if (!$cache) {
        $cache = new Cache();
    }

    return $cache;
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function route(string $path): string
{
    return '/' . ltrim($path, '/');
}

function session_flash(string $key, ?string $value = null)
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sanitize_html(string $value, ?array $allowedTags = null): string
{
    $defaultTags = ['p', 'ul', 'ol', 'li', 'strong', 'em', 'a', 'br', 'h2', 'h3', 'h4', 'blockquote', 'code', 'pre'];
    $allowed = $allowedTags ?? $defaultTags;
    $allowedString = $allowed ? '<' . implode('><', $allowed) . '>' : '';

    return strip_tags($value, $allowedString);
}

function rate_limit(string $key, int $maxAttempts, int $seconds): bool
{
    $cacheKey = 'rate_' . md5($key . $_SERVER['REMOTE_ADDR']);
    $attempts = cache()->remember($cacheKey, $seconds, fn () => 0);

    if ($attempts >= $maxAttempts) {
        return false;
    }

    cache()->forget($cacheKey);
    cache()->remember($cacheKey, $seconds, fn () => $attempts + 1);
    return true;
}
