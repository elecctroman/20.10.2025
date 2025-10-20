<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected string $table = 'products';

    public function allActive(): array
    {
        $stmt = $this->query(
            'SELECT p.*, (SELECT MIN(v.price) FROM variants v WHERE v.product_id = p.id AND v.is_active = 1) AS price FROM products p WHERE p.is_active = 1 ORDER BY p.name'
        );
        $products = $stmt->fetchAll();

        return array_map(function (array $product) {
            $product['price'] = (float) ($product['price'] ?? 0);
            $product['auto_delivery'] = $product['delivery_mode'] !== 'manual';
            return $product;
        }, $products);
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query('SELECT * FROM products WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        $variantStmt = $this->query(
            'SELECT * FROM variants WHERE product_id = :product_id AND is_active = 1 ORDER BY id ASC LIMIT 1',
            ['product_id' => $product['id']]
        );
        $variant = $variantStmt->fetch();

        if ($variant) {
            $product['price'] = (float) $variant['price'];
            $product['compare_at_price'] = $variant['compare_at_price'];
            $product['variant_id'] = (int) $variant['id'];
            $product['stock_visible'] = (int) $variant['stock_visible'];
        } else {
            $product['price'] = 0.0;
            $product['compare_at_price'] = null;
            $product['variant_id'] = null;
            $product['stock_visible'] = 0;
        }

        $product['auto_delivery'] = $product['delivery_mode'] !== 'manual';

        return $product;
    }
}
