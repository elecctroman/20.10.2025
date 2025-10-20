<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected string $table = 'products';

    public function allActive(): array
    {
        $stmt = $this->query(
            'SELECT p.*, 
                (SELECT MIN(v.price) FROM variants v WHERE v.product_id = p.id AND v.is_active = 1) AS price,
                (SELECT MAX(v.stock_visible) FROM variants v WHERE v.product_id = p.id AND v.is_active = 1) AS stock_visible
             FROM products p WHERE p.is_active = 1 ORDER BY p.name'
        );
        $products = $stmt->fetchAll();

        return array_map(function (array $product) {
            $product['price'] = (float) ($product['price'] ?? 0);
            $product['auto_delivery'] = $product['delivery_mode'] !== 'manual';
            $product['stock_visible'] = (int) ($product['stock_visible'] ?? 0);
            return $product;
        }, $products);
    }

    public function byCategory(int $categoryId): array
    {
        return $this->filterByCategory($categoryId, [], 1, 100)['items'];
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
             WHERE p.is_active = 1 AND (p.name LIKE :term OR p.short_desc LIKE :term OR p.slug LIKE :term OR p.tags LIKE :term)
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

    public function filterByCategory(int $categoryId, array $filters, int $page = 1, int $perPage = 15): array
    {
        $page = max(1, (int) $page);
        $perPage = max(1, (int) $perPage);
        $offset = ($page - 1) * $perPage;

        $conditions = ['p.is_active = 1', 'p.category_id = :category_id'];
        $params = ['category_id' => $categoryId];

        $delivery = $filters['delivery'] ?? null;
        if (in_array($delivery, ['auto', 'instant', 'manual'], true)) {
            $conditions[] = 'p.delivery_mode = :delivery';
            $params['delivery'] = $delivery;
        }

        if (!empty($filters['variant'])) {
            $conditions[] = 'v.name LIKE :variant';
            $params['variant'] = '%' . $filters['variant'] . '%';
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $havingParts = [];
        if (isset($filters['price_min']) && is_numeric($filters['price_min'])) {
            $havingParts[] = 'price >= :price_min';
            $params['price_min'] = (float) $filters['price_min'];
        }

        if (isset($filters['price_max']) && is_numeric($filters['price_max'])) {
            $havingParts[] = 'price <= :price_max';
            $params['price_max'] = (float) $filters['price_max'];
        }

        $having = $havingParts ? ' HAVING ' . implode(' AND ', $havingParts) : '';

        $order = $filters['sort'] ?? 'featured';
        switch ($order) {
            case 'price_desc':
                $orderBy = ' ORDER BY (price IS NULL), price DESC';
                break;
            case 'price_asc':
                $orderBy = ' ORDER BY (price IS NULL), price ASC';
                break;
            case 'newest':
                $orderBy = ' ORDER BY p.created_at DESC';
                break;
            default:
                $orderBy = ' ORDER BY p.position ASC, p.name ASC';
                break;
        }

        $base = "FROM products p LEFT JOIN variants v ON v.product_id = p.id AND v.is_active = 1 {$where} GROUP BY p.id";

        $countSql = "SELECT COUNT(*) FROM (SELECT p.id {$base}{$having}) AS aggregate";
        $total = (int) $this->query($countSql, $params)->fetchColumn();

        $limit = sprintf(' LIMIT %d OFFSET %d', $perPage, $offset);
        $selectSql = "SELECT p.*, MIN(v.price) AS price, MAX(v.stock_visible) AS stock_visible, GROUP_CONCAT(DISTINCT v.name ORDER BY v.price SEPARATOR ',') AS variant_names {$base}{$having}{$orderBy}{$limit}";
        $items = $this->query($selectSql, $params)->fetchAll();

        $items = array_map(function (array $item) {
            $item['price'] = (float) ($item['price'] ?? 0);
            $item['stock_visible'] = (int) ($item['stock_visible'] ?? 0);
            $item['variant_names'] = $item['variant_names'] ? array_map('trim', explode(',', $item['variant_names'])) : [];
            $item['auto_delivery'] = ($item['delivery_mode'] ?? '') !== 'manual';
            return $item;
        }, $items);

        $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => max(1, $pages),
            'per_page' => $perPage,
        ];
    }

    public function variantNamesByCategory(int $categoryId): array
    {
        $stmt = $this->query(
            'SELECT DISTINCT v.name
             FROM variants v
             INNER JOIN products p ON p.id = v.product_id
             WHERE v.is_active = 1 AND p.is_active = 1 AND p.category_id = :category_id
             ORDER BY v.price ASC',
            ['category_id' => $categoryId]
        );

        return array_map(fn ($row) => $row['name'], $stmt->fetchAll());
    }

    public function priceRangeByCategory(int $categoryId): array
    {
        $stmt = $this->query(
            'SELECT MIN(v.price) AS min_price, MAX(v.price) AS max_price
             FROM variants v
             INNER JOIN products p ON p.id = v.product_id
             WHERE v.is_active = 1 AND p.is_active = 1 AND p.category_id = :category_id',
            ['category_id' => $categoryId]
        );

        $row = $stmt->fetch() ?: [];

        return [
            'min' => isset($row['min_price']) ? (float) $row['min_price'] : 0.0,
            'max' => isset($row['max_price']) ? (float) $row['max_price'] : 0.0,
        ];
    }
}
