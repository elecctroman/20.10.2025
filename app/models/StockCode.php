<?php
namespace App\Models;

use App\Core\Model;

class StockCode extends Model
{
    public function pull(int $productId): ?array
    {
        $stmt = $this->query('SELECT * FROM stock_codes WHERE product_id = :product_id AND delivered_at IS NULL ORDER BY id ASC LIMIT 1', ['product_id' => $productId]);
        $code = $stmt->fetch();

        if ($code) {
            $this->query('UPDATE stock_codes SET delivered_at = NOW() WHERE id = :id', ['id' => $code['id']]);
        }

        return $code ?: null;
    }

    public function remainingCount(int $productId): int
    {
        $stmt = $this->query('SELECT COUNT(*) AS total FROM stock_codes WHERE product_id = :product_id AND delivered_at IS NULL', ['product_id' => $productId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
