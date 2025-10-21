<?php
return [
    'name' => 'E-Pin Market',
    'env' => 'production',
    'debug' => false,
    'url' => 'https://example.com',
    'timezone' => 'Europe/Istanbul',
    'locale' => 'tr',
    'fallback_locale' => 'en',
    'encryption_key' => bin2hex(random_bytes(16)),
    'session_name' => 'epin_session',
    'cache_path' => __DIR__ . '/../../storage/cache',
    'log_path' => __DIR__ . '/../../storage/logs/app.log',
    'whatsapp_link' => 'https://wa.me/905550000000',
];
