<?php
namespace App\Models;

use App\Core\Model;

class Coupon extends Model
{
    public function findValid(string $code): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM coupons WHERE code = :code AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1',
            ['code' => $code]
        );
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return null;
        }

        if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) {
            return null;
        }

        return $coupon;
    }
}
