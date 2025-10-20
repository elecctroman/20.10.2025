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

    public function byCategory(int $categoryId): array
    {
        $stmt = $this->query(
            'SELECT p.*, (SELECT MIN(v.price) FROM variants v WHERE v.product_id = p.id AND v.is_active = 1) AS price
             FROM products p
             WHERE p.is_active = 1 AND p.category_id = :category_id
             ORDER BY p.name',
            ['category_id' => $categoryId]
        );

        return array_map(function (array $product) {
            $product['price'] = (float) ($product['price'] ?? 0);
            $product['auto_delivery'] = $product['delivery_mode'] !== 'manual';
            return $product;
        }, $stmt->fetchAll());
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->query('SELECT * FROM products WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        return $this->hydrateWithPrimaryVariant($product);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->query('SELECT * FROM products WHERE id = :id LIMIT 1', ['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        return $this->hydrateWithPrimaryVariant($product);
    }

    protected function hydrateWithPrimaryVariant(array $product): array
    {
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

    public function related(int $categoryId, int $excludeId, int $limit = 4): array
    {
        $limit = max(1, (int) $limit);
        $sql = sprintf(
            'SELECT p.*, (SELECT MIN(v.price) FROM variants v WHERE v.product_id = p.id AND v.is_active = 1) AS price
             FROM products p
             WHERE p.is_active = 1 AND p.category_id = :category_id AND p.id != :exclude
             ORDER BY p.created_at DESC
             LIMIT %d',
            $limit
        );

        $stmt = $this->query($sql, ['category_id' => $categoryId, 'exclude' => $excludeId]);

        return array_map(function (array $product) {
            $product['price'] = (float) ($product['price'] ?? 0);
            return $product;
        }, $stmt->fetchAll());
    }

    public function search(string $term, int $limit = 20): array
    {
        $limit = max(1, (int) $limit);
        $sql = sprintf(
            'SELECT p.*, (SELECT MIN(v.price) FROM variants v WHERE v.product_id = p.id AND v.is_active = 1) AS price
             FROM products p
             WHERE p.is_active = 1 AND (p.name LIKE :term OR p.short_desc LIKE :term)
             ORDER BY p.name
             LIMIT %d',
            $limit
        );

        $stmt = $this->query($sql, ['term' => '%' . $term . '%']);

        return array_map(function (array $product) {
            $product['price'] = (float) ($product['price'] ?? 0);
            return $product;
        }, $stmt->fetchAll());
    }
}
