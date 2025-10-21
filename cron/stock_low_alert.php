<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Models\Product;
use App\Models\StockCode;
use App\Core\Mailer;

$productModel = new Product();
$stockModel = new StockCode();
$mailer = new Mailer();

$products = $productModel->allActive();

foreach ($products as $product) {
    $remaining = $stockModel->remainingCount((int) $product['id']);
    if ($remaining <= 3) {
        $mailer->send(config('mail.from.address'), 'Düşük Stok Uyarısı', 'store/payment_result', [
            'message' => $product['name'] . ' ürünü için stok ' . $remaining . ' adet kaldı.',
        ]);
    }
}
