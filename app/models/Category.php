<?php
namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    public function all(): array
    {
        $stmt = $this->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY position, name');
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query('SELECT * FROM categories WHERE slug = :slug AND is_active = 1 LIMIT 1', ['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}
