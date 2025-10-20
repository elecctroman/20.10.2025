<?php
namespace App\Models;

use App\Core\Model;

class Blog extends Model
{
    public function allPublished(): array
    {
        $stmt = $this->query('SELECT * FROM posts WHERE status = 1 ORDER BY published_at DESC');
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query('SELECT * FROM posts WHERE slug = :slug AND status = 1 LIMIT 1', ['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}
