<?php
namespace App\Models;

use App\Core\Model;

class StockCode extends Model
{
    public function createMany(int $productId, ?int $variantId, array $codes): int
    {
        $codes = array_values(array_filter(array_map(fn ($code) => trim($code), $codes), fn ($code) => $code !== ''));

        if (empty($codes)) {
            return 0;
        }

        $inserted = 0;
        $stmt = database()->prepare(
            'INSERT INTO stock_codes (product_id, variant_id, code, is_used, created_at) VALUES (:product_id, :variant_id, :code, 0, NOW())'
        );

        foreach ($codes as $code) {
            $stmt->execute([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'code' => $code,
            ]);
            $inserted++;
        }

        return $inserted;
    }

    public function createAndAssign(int $productId, ?int $variantId, array $codes, int $orderId): array
    {
        $codes = array_values(array_filter(array_map(fn ($code) => trim($code), $codes), fn ($code) => $code !== ''));

        if (empty($codes)) {
            return [];
        }

        $insertStmt = database()->prepare(
            'INSERT INTO stock_codes (product_id, variant_id, code, is_used, used_at, order_id, created_at) VALUES (:product_id, :variant_id, :code, 1, NOW(), :order_id, NOW())'
        );

        $delivered = [];

        foreach ($codes as $code) {
            $insertStmt->execute([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'code' => $code,
                'order_id' => $orderId,
            ]);

            $delivered[] = ['code' => $code];
        }

        return $delivered;
    }

    public function list(array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['product_id'])) {
            $conditions[] = 'sc.product_id = :product_id';
            $params['product_id'] = (int) $filters['product_id'];
        }

        if (array_key_exists('variant_id', $filters) && $filters['variant_id'] !== '' && $filters['variant_id'] !== null) {
            if ($filters['variant_id'] === 'none') {
                $conditions[] = 'sc.variant_id IS NULL';
            } else {
                $conditions[] = 'sc.variant_id = :variant_id';
                $params['variant_id'] = (int) $filters['variant_id'];
            }
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'available') {
                $conditions[] = 'sc.is_used = 0';
            } elseif ($filters['status'] === 'assigned') {
                $conditions[] = 'sc.is_used = 1 AND sc.order_id IS NOT NULL';
            } elseif ($filters['status'] === 'consumed') {
                $conditions[] = 'sc.is_used = 1';
            }
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT sc.*, p.name AS product_name, v.name AS variant_name
                FROM stock_codes sc
                LEFT JOIN products p ON p.id = sc.product_id
                LEFT JOIN variants v ON v.id = sc.variant_id
                $where
                ORDER BY sc.created_at DESC";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function pull(int $productId, ?int $variantId = null, ?int $orderId = null): ?array
    {
        $params = ['product_id' => $productId];
        $variantClause = '';
        if ($variantId !== null) {
            $variantClause = ' AND variant_id = :variant_id';
            $params['variant_id'] = $variantId;
        }

        $stmt = $this->query(
            'SELECT * FROM stock_codes WHERE product_id = :product_id' . $variantClause . ' AND is_used = 0 ORDER BY id ASC LIMIT 1',
            $params
        );
        $code = $stmt->fetch();

        if ($code) {
            $updateParams = ['id' => $code['id']];
            $setOrder = '';
            if ($orderId !== null) {
                $setOrder = ', order_id = :order_id';
                $updateParams['order_id'] = $orderId;
            }

            $this->query(
                'UPDATE stock_codes SET is_used = 1, used_at = NOW()' . $setOrder . ' WHERE id = :id',
                $updateParams
            );
        }

        return $code ?: null;
    }

    public function remainingCount(int $productId, ?int $variantId = null): int
    {
        $params = ['product_id' => $productId];
        $variantClause = '';
        if ($variantId !== null) {
            $variantClause = ' AND variant_id = :variant_id';
            $params['variant_id'] = $variantId;
        }

        $stmt = $this->query(
            'SELECT COUNT(*) AS total FROM stock_codes WHERE product_id = :product_id' . $variantClause . ' AND is_used = 0',
            $params
        );
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
