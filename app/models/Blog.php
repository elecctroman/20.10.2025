<?php
namespace App\Models;

use App\Core\Model;

class Blog extends Model
{
    public function allPublished(): array
    {
        $stmt = $this->query(
            'SELECT * FROM blog_posts WHERE is_active = 1 AND (published_at IS NULL OR published_at <= NOW()) ORDER BY published_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM blog_posts WHERE slug = :slug AND is_active = 1 LIMIT 1',
            ['slug' => $slug]
        );
        return $stmt->fetch() ?: null;
    }
}
