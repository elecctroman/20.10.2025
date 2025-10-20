<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected string $table = 'products';

    public function allActive(): array
    {
        $stmt = $this->query('SELECT * FROM products WHERE status = 1 ORDER BY sort_order');
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query('SELECT * FROM products WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
        $product = $stmt->fetch();
        return $product ?: null;
    }
}
