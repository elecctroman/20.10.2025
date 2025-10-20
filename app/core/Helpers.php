<?php
use App\Core\Cache;
use App\Core\CSRF;
use App\Models\Setting;

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

function audit(string $action, array $meta = []): void
{
    $userId = $_SESSION['user_id'] ?? null;
    $stmt = database()->prepare('INSERT INTO audit_logs (user_id, action, meta_json, created_at) VALUES (:user_id, :action, :meta_json, NOW())');
    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE),
    ]);
}

function settings(?string $key = null, $default = null)
{
    static $settings;
    if ($settings === null) {
        try {
            $settings = (new Setting())->all();
        } catch (Throwable $e) {
            $settings = [];
        }
    }

    if ($key === null) {
        return $settings;
    }

    return $settings[$key] ?? $default;
}

function currency_rates(): array
{
    static $rates;
    if ($rates !== null) {
        return $rates;
    }

    $rates = cache()->remember('ecb_rates', 43200, function () {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
            ],
            'https' => [
                'timeout' => 5,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if (!$content) {
            return ['EUR' => 1.0];
        }

        $xml = @simplexml_load_string($content);
        if (!$xml) {
            return ['EUR' => 1.0];
        }

        $data = ['EUR' => 1.0];
        if (isset($xml->Cube->Cube)) {
            foreach ($xml->Cube->Cube->Cube as $cube) {
                $currency = (string) $cube['currency'];
                $rate = (float) $cube['rate'];
                if ($currency) {
                    $data[$currency] = $rate;
                }
            }
        }

        return $data;
    });

    return $rates;
}

function convert_price_multi(float $amount): array
{
    $amount = max(0.0, (float) $amount);
    $conversions = [
        'TRY' => round($amount, 2),
    ];

    $rates = currency_rates();
    $eurRate = $rates['TRY'] ?? null;

    if ($eurRate && $eurRate > 0) {
        $eurValue = $amount / $eurRate;
        $conversions['EUR'] = round($eurValue, 2);

        if (!empty($rates['USD'])) {
            $usdValue = $eurValue * (float) $rates['USD'];
            $conversions['USD'] = round($usdValue, 2);
        }
    }

    return $conversions;
}
