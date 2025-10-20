<?php
namespace App\Models;

use App\Core\Model;

class Variant extends Model
{
    public function byProduct(int $productId): array
    {
        $stmt = $this->query('SELECT * FROM product_variants WHERE product_id = :product_id ORDER BY sort_order', ['product_id' => $productId]);
        return $stmt->fetchAll();
    }
}
