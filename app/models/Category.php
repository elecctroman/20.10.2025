<?php
namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    public function all(): array
    {
        $stmt = $this->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query('SELECT * FROM categories WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}
