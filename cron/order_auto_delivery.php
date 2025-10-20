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
                $deliveryPayload[] = ['code' => $code['code']];
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
                $mailer->send($email, 'Sipariş Teslim Edildi', 'store/payment_result', [
                    'message' => 'Siparişiniz otomatik teslim edildi.',
                ]);
            }
        }
    }
}
