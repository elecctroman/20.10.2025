<?php
namespace App\Models;

use App\Core\Model;

class Coupon extends Model
{
    public function findValid(string $code): ?array
    {
        $stmt = $this->query('SELECT * FROM coupons WHERE code = :code AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1', ['code' => $code]);
        return $stmt->fetch() ?: null;
    }
}
