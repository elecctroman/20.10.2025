<?php
namespace App\Models;

use App\Core\Model;

class Page extends Model
{
    protected string $table = 'pages';

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query('SELECT * FROM pages WHERE slug = :slug AND is_active = 1 LIMIT 1', ['slug' => $slug]);
        $page = $stmt->fetch();

        return $page ?: null;
    }

    public function allActive(): array
    {
        $stmt = $this->query('SELECT * FROM pages WHERE is_active = 1 ORDER BY title');
        return $stmt->fetchAll();
    }
}
