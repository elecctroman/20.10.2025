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

    public function find(int $orderId): ?array
    {
        $stmt = $this->query('SELECT * FROM orders WHERE id = :id LIMIT 1', ['id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    public function items(int $orderId): array
    {
        $stmt = $this->query(
            'SELECT oi.*, p.name AS product_name, p.delivery_mode, p.requires_input, v.name AS variant_name
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             LEFT JOIN variants v ON v.id = oi.variant_id
             WHERE oi.order_id = :order_id',
            ['order_id' => $orderId]
        );

        return $stmt->fetchAll();
    }

    public function allPendingManual(): array
    {
        $stmt = $this->query(
            "SELECT o.*, u.email, u.name, u.surname
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             WHERE o.status IN ('paid','processing')"
        );
        $orders = $stmt->fetchAll();

        $pending = [];
        foreach ($orders as $order) {
            $items = $this->items($order['id']);
            $order['items'] = [];

            foreach ($items as $item) {
                $deliveries = json_decode($item['delivery_json'] ?? '', true) ?? [];
                $deliveredCodes = array_filter($deliveries, fn ($entry) => !empty($entry['code']));
                $item['delivered_count'] = count($deliveredCodes);
                $item['remaining'] = max(0, (int) $item['qty'] - $item['delivered_count']);

                if (empty($item['delivery_json']) || $item['remaining'] > 0) {
                    $order['items'][] = $item;
                }
            }

            if (!empty($order['items'])) {
                $pending[] = $order;
            }
        }

        return $pending;
    }
}
