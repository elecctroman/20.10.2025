<?php
namespace App\Models;

use App\Core\Model;

class StockCode extends Model
{
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
