<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Models\Order;
use App\Models\StockCode;
use App\Core\Mailer;

$orderModel = new Order();
$stockModel = new StockCode();
$mailer = new Mailer();

$orders = database()->query("SELECT * FROM orders WHERE status = 'paid'")->fetchAll();

foreach ($orders as $order) {
    $itemsStmt = database()->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
    $itemsStmt->execute(['order_id' => $order['id']]);
    $items = $itemsStmt->fetchAll();
    foreach ($items as $item) {
        $code = $stockModel->pull((int) $item['product_id']);
        if ($code) {
            database()->prepare('UPDATE order_items SET delivered_code = :code WHERE id = :id')
                ->execute(['code' => $code['code'], 'id' => $item['id']]);
        }
    }

    database()->prepare("UPDATE orders SET status = 'completed' WHERE id = :id")
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
