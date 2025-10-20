<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\Order;
use App\Models\StockCode;
use App\Services\PaymentGateway;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            session_flash('error', 'Sepetiniz boş.');
            return $this->redirect('/cart');
        }

        return $this->view('store/checkout', ['cart' => $cart]);
    }

    public function process()
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/checkout');
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            session_flash('error', 'Sepetiniz boş.');
            return $this->redirect('/cart');
        }

        $errors = Validator::make($_POST, [
            'payment_method' => 'required',
            'email' => 'required|email',
        ]);

        if ($errors) {
            session_flash('error', 'Ödeme formunu kontrol ediniz.');
            return $this->redirect('/checkout');
        }

        $total = array_reduce($cart, fn ($sum, $item) => $sum + (($item['product']['price'] ?? 0) * $item['quantity']), 0.0);

        $gateway = new PaymentGateway();
        $result = $gateway->charge($_POST['payment_method'], [
            'amount' => $total,
            'email' => $_POST['email'],
            'description' => 'E-Pin Satışı',
        ]);

        if (!$result['success']) {
            session_flash('error', $result['message'] ?? 'Ödeme başarısız.');
            return $this->redirect('/checkout');
        }

        $orderModel = new Order();
        $orderId = $orderModel->create([
            'user_id' => Auth::user()['id'] ?? null,
            'total' => $total,
            'status' => 'paid',
            'currency' => 'TRY',
            'customer_note' => $_POST['customer_note'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $paymentStmt = database()->prepare(
            'INSERT INTO payments (order_id, gateway, status, amount, txn_id, payload, created_at) VALUES (:order_id, :gateway, :status, :amount, :txn_id, :payload, NOW())'
        );
        $paymentStmt->execute([
            'order_id' => $orderId,
            'gateway' => $_POST['payment_method'],
            'status' => 'success',
            'amount' => $total,
            'txn_id' => $result['transaction_id'] ?? null,
            'payload' => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);

        $itemStmt = database()->prepare(
            'INSERT INTO order_items (order_id, product_id, variant_id, qty, unit_price, requires_input_value, delivery_json) VALUES (:order_id, :product_id, :variant_id, :qty, :unit_price, :requires_input_value, :delivery_json)'
        );

        $stockModel = new StockCode();
        $allDelivered = true;

        foreach ($cart as $item) {
            $product = $item['product'];
            $variantId = $product['variant_id'] ?? null;
            $deliveryPayload = [];

            if (!empty($product['auto_delivery'])) {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $code = $stockModel->pull((int) $product['id'], $variantId ? (int) $variantId : null, $orderId);
                    if ($code) {
                        $deliveryPayload[] = ['code' => $code['code']];
                    }
                }
                if (count($deliveryPayload) < $item['quantity']) {
                    $allDelivered = false;
                }
            } else {
                $allDelivered = false;
            }

            $itemStmt->execute([
                'order_id' => $orderId,
                'product_id' => $product['id'],
                'variant_id' => $variantId,
                'qty' => $item['quantity'],
                'unit_price' => $product['price'] ?? 0,
                'requires_input_value' => $item['input_value'] ?? null,
                'delivery_json' => $deliveryPayload ? json_encode($deliveryPayload, JSON_UNESCAPED_UNICODE) : null,
            ]);
        }

        $nextStatus = $allDelivered ? 'delivered' : 'processing';
        database()->prepare('UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id')
            ->execute(['status' => $nextStatus, 'id' => $orderId]);

        $_SESSION['cart'] = [];
        session_flash('success', 'Siparişiniz oluşturuldu.');

        return $this->redirect('/order/' . $orderId);
    }
}
