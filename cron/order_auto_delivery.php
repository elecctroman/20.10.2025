<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Models\StockCode;
use App\Core\Mailer;

$stockModel = new StockCode();
$mailer = new Mailer();

$orders = database()->query("SELECT * FROM orders WHERE status IN ('paid','processing')")->fetchAll();

foreach ($orders as $order) {
    $itemsStmt = database()->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
    $itemsStmt->execute(['order_id' => $order['id']]);
    $items = $itemsStmt->fetchAll();

    $allDelivered = true;

    foreach ($items as $item) {
        if (!empty($item['delivery_json'])) {
            continue;
        }

        $deliveryPayload = [];
        for ($i = 0; $i < $item['qty']; $i++) {
            $code = $stockModel->pull((int) $item['product_id'], $item['variant_id'] ? (int) $item['variant_id'] : null, (int) $order['id']);
            if ($code) {
                $deliveryPayload[] = ['code' => base64_encode($code['code'])];
            }
        }

        if ($deliveryPayload) {
            database()->prepare('UPDATE order_items SET delivery_json = :delivery_json WHERE id = :id')
                ->execute([
                    'delivery_json' => json_encode($deliveryPayload, JSON_UNESCAPED_UNICODE),
                    'id' => $item['id'],
                ]);
        } else {
            $allDelivered = false;
        }
    }

    if ($allDelivered) {
        database()->prepare("UPDATE orders SET status = 'delivered', updated_at = NOW() WHERE id = :id")
            ->execute(['id' => $order['id']]);

        if ($order['user_id']) {
            $userStmt = database()->prepare('SELECT email FROM users WHERE id = :id');
            $userStmt->execute(['id' => $order['user_id']]);
            $email = $userStmt->fetchColumn();
            if ($email) {
                $itemsStmt = database()->prepare(
                    'SELECT oi.*, p.name AS product_name, v.name AS variant_name
                     FROM order_items oi
                     LEFT JOIN products p ON p.id = oi.product_id
                     LEFT JOIN variants v ON v.id = oi.variant_id
                     WHERE oi.order_id = :order_id'
                );
                $itemsStmt->execute(['order_id' => $order['id']]);
                $items = $itemsStmt->fetchAll();
                $payloads = [];
                foreach ($items as $orderItem) {
                    if (empty($orderItem['delivery_json'])) {
                        continue;
                    }
                    $payloads[] = [
                        'product' => ['name' => $orderItem['product_name'] ?? 'Ürün'],
                        'variant_name' => $orderItem['variant_id'] ? ($orderItem['variant_name'] ?? '') : null,
                        'items' => json_decode($orderItem['delivery_json'], true) ?? [],
                    ];
                }

                if (!empty($payloads)) {
                    $mailer->send($email, 'Sipariş Teslim Edildi #' . $order['id'], 'emails/order_delivery', [
                        'orderId' => $order['id'],
                        'payloads' => $payloads,
                        'total' => $order['total'],
                    ]);
                }
            }
        }
    }
}
