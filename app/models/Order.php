<?php
namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    public function create(array $data): int
    {
        $stmt = database()->prepare('INSERT INTO orders (user_id, total_amount, status, payment_method, created_at, updated_at) VALUES (:user_id, :total_amount, :status, :payment_method, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'total_amount' => $data['total_amount'],
            'status' => $data['status'],
            'payment_method' => $data['payment_method'],
        ]);

        return (int) database()->lastInsertId();
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->query('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC', ['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
