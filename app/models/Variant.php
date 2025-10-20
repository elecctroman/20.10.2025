<?php
namespace App\Models;

use App\Core\Model;

class Variant extends Model
{
    public function byProduct(int $productId): array
    {
        $stmt = $this->query(
            'SELECT * FROM variants WHERE product_id = :product_id AND is_active = 1 ORDER BY id',
            ['product_id' => $productId]
        );
        return $stmt->fetchAll();
    }
}
