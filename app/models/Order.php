<?php
namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    public function create(array $data): int
    {
        $stmt = database()->prepare(
            'INSERT INTO orders (user_id, status, total, currency, customer_note, admin_note, ip, created_at, updated_at) VALUES (:user_id, :status, :total, :currency, :customer_note, :admin_note, :ip, NOW(), NOW())'
        );
        $stmt->execute([
            'user_id' => $data['user_id'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'total' => $data['total'] ?? 0.00,
            'currency' => $data['currency'] ?? 'TRY',
            'customer_note' => $data['customer_note'] ?? null,
            'admin_note' => $data['admin_note'] ?? null,
            'ip' => $data['ip'] ?? null,
        ]);

        return (int) database()->lastInsertId();
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->query('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC', ['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
