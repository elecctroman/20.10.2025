<?php
namespace App\Core;

class Upload
{
    protected array $allowedMime = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    public function handle(array $file, string $directory): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $this->allowedMime, true)) {
            throw new \RuntimeException('Geçersiz dosya türü.');
        }

        $filename = uniqid('upload_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $path = __DIR__ . '/../../storage/uploads/' . trim($directory, '/') . '/' . $filename;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new \RuntimeException('Dosya yüklenemedi.');
        }

        return $filename;
    }
}
