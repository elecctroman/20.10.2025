<?php
namespace App\Core;

class Cache
{
    protected string $path;

    public function __construct()
    {
        $this->path = config('app.cache_path');

        if (!is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }
    }

    public function remember(string $key, int $seconds, callable $callback)
    {
        $file = $this->path . '/' . md5($key) . '.cache.php';

        if (file_exists($file) && (filemtime($file) + $seconds) > time()) {
            return unserialize(file_get_contents($file));
        }

        $value = $callback();
        file_put_contents($file, serialize($value));

        return $value;
    }

    public function forget(string $key): void
    {
        $file = $this->path . '/' . md5($key) . '.cache.php';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
