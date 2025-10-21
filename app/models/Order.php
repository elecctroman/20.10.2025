<?php
namespace App\Models;

use App\Core\Model;
use PDO;

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

    public function aggregate(?string $start = null, ?string $end = null): array
    {
        $conditions = [];
        $params = [];

        if ($start) {
            $conditions[] = 'created_at >= :start';
            $params['start'] = $start;
        }

        if ($end) {
            $conditions[] = 'created_at < :end';
            $params['end'] = $end;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->query("SELECT COUNT(*) AS count, COALESCE(SUM(total),0) AS total FROM orders $where", $params);
        $row = $stmt->fetch() ?: ['count' => 0, 'total' => 0];

        $count = (int) ($row['count'] ?? 0);
        $total = (float) ($row['total'] ?? 0);

        return [
            'count' => $count,
            'total' => $total,
            'average' => $count > 0 ? $total / $count : 0.0,
        ];
    }

    public function statusBreakdown(?string $start = null, ?string $end = null): array
    {
        $conditions = [];
        $params = [];

        if ($start) {
            $conditions[] = 'created_at >= :start';
            $params['start'] = $start;
        }

        if ($end) {
            $conditions[] = 'created_at < :end';
            $params['end'] = $end;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->query("SELECT status, COUNT(*) AS total FROM orders $where GROUP BY status", $params);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['status']] = (int) $row['total'];
        }

        return $result;
    }

    public function recentWithUser(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $stmt = $this->query(
            'SELECT o.*, u.email, u.name, u.surname FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT ' . (int) $limit
        );

        return $stmt->fetchAll();
    }

    public function topSellersSince(string $since): array
    {
        $stmt = $this->query(
            "SELECT p.name, SUM(oi.qty) AS quantity, SUM(oi.qty * oi.unit_price) AS revenue
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             JOIN products p ON p.id = oi.product_id
             WHERE o.created_at >= :since
             GROUP BY p.name
             ORDER BY quantity DESC
             LIMIT 10",
            ['since' => $since]
        );

        return $stmt->fetchAll();
    }

    public function adminList(array $filters, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'o.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['gateway'])) {
            $conditions[] = 'EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.id AND p.gateway = :gateway)';
            $params['gateway'] = $filters['gateway'];
        }

        if (!empty($filters['from'])) {
            $conditions[] = 'o.created_at >= :from';
            $params['from'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $conditions[] = 'o.created_at <= :to';
            $params['to'] = $filters['to'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = '(o.id = :id_query OR u.email LIKE :like_query OR CONCAT_WS(" ", u.name, u.surname) LIKE :like_query)';
            $params['id_query'] = ctype_digit($filters['q']) ? (int) $filters['q'] : 0;
            $params['like_query'] = '%' . $filters['q'] . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $baseSql = "FROM orders o LEFT JOIN users u ON u.id = o.user_id $where";

        $countStmt = database()->prepare('SELECT COUNT(*) ' . $baseSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT o.*, u.email, u.name, u.surname,
                (SELECT gateway FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS last_gateway,
                (SELECT status FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS payment_status
                $baseSql
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = database()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    public function detail(int $orderId): ?array
    {
        $stmt = $this->query(
            'SELECT o.*, u.email, u.name, u.surname FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = :id LIMIT 1',
            ['id' => $orderId]
        );

        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $order['items'] = $this->items($orderId);
        $order['payments'] = $this->query('SELECT * FROM payments WHERE order_id = :order_id ORDER BY created_at DESC', ['order_id' => $orderId])->fetchAll();

        return $order;
    }

    public function updateStatus(int $orderId, string $status, ?string $adminNote = null): bool
    {
        $stmt = $this->query(
            'UPDATE orders SET status = :status, admin_note = :admin_note, updated_at = NOW() WHERE id = :id',
            [
                'status' => $status,
                'admin_note' => $adminNote,
                'id' => $orderId,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    public function deliveryPayloads(array $items): array
    {
        $payloads = [];

        foreach ($items as $item) {
            $deliveries = json_decode($item['delivery_json'] ?? '', true) ?? [];
            if (empty($deliveries)) {
                continue;
            }

            $payloads[] = [
                'product' => ['name' => $item['product_name']],
                'variant_name' => $item['variant_name'],
                'items' => $deliveries,
            ];
        }

        return $payloads;
    }
}
