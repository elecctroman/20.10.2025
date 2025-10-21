<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Validator;
use App\Core\Mailer;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockCode;
use App\Models\Variant;
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

        $totals = $this->calculateTotals($cart, $_SESSION['coupon'] ?? null);

        return $this->view('store/checkout', [
            'cart' => $cart,
            'user' => Auth::user(),
            'totals' => $totals,
            'coupon' => $_SESSION['coupon'] ?? null,
        ]);
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

        $rules = [
            'payment_method' => 'required',
            'email' => 'required|email',
            'terms' => 'accepted',
        ];

        $errors = Validator::make($_POST, $rules);

        if ($errors) {
            session_flash('error', 'Ödeme formunu kontrol ediniz.');
            return $this->redirect('/checkout');
        }

        $coupon = $_SESSION['coupon'] ?? null;
        $totals = $this->calculateTotals($cart, $coupon);
        $total = $totals['total'];

        $paymentMethod = $_POST['payment_method'];
        $gateway = new PaymentGateway();
        $result = ['success' => true, 'transaction_id' => null];

        if ($paymentMethod === 'wallet') {
            if (!Auth::check()) {
                session_flash('error', 'Cüzdan kullanmak için giriş yapmalısınız.');
                return $this->redirect('/checkout');
            }

            $user = Auth::user();
            if (($user['balance'] ?? 0) < $total) {
                session_flash('error', 'Cüzdan bakiyeniz yetersiz.');
                return $this->redirect('/checkout');
            }

            database()->prepare('UPDATE users SET balance = balance - :amount WHERE id = :id')
                ->execute(['amount' => $total, 'id' => $user['id']]);
            Auth::sync();
            audit('wallet_payment', ['order_total' => $total]);
        } else {
            $result = $gateway->charge($paymentMethod, [
                'amount' => $total,
                'email' => $_POST['email'],
                'description' => 'E-Pin Satışı',
            ]);
        }

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
            'gateway' => $paymentMethod,
            'status' => 'success',
            'amount' => $total,
            'txn_id' => $result['transaction_id'] ?? null,
            'payload' => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);

        $itemStmt = database()->prepare(
            'INSERT INTO order_items (order_id, product_id, variant_id, qty, unit_price, requires_input_value, delivery_json) VALUES (:order_id, :product_id, :variant_id, :qty, :unit_price, :requires_input_value, :delivery_json)'
        );

        $productModel = new Product();
        $variantModel = new Variant();
        $stockModel = new StockCode();
        $mailer = new Mailer();

        $allDelivered = true;
        $deliveredPayloads = [];

        foreach ($cart as $item) {
            $productData = $productModel->findById((int) $item['product']['id']);
            if (!$productData) {
                continue;
            }

            $variantId = null;
            $variantName = null;
            $unitPrice = (float) $productData['price'];

            if (!empty($item['product']['variant_id'])) {
                $variant = $variantModel->findActive((int) $item['product']['variant_id']);
                if ($variant) {
                    $variantId = (int) $variant['id'];
                    $variantName = $variant['name'];
                    $unitPrice = (float) $variant['price'];
                }
            }

            $deliveryPayload = [];
            $lineDelivered = true;

            if ($productData['delivery_mode'] === 'auto') {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $code = $stockModel->pull((int) $productData['id'], $variantId, $orderId);
                    if ($code) {
                        $encoded = base64_encode($code['code']);
                        $deliveryPayload[] = ['code' => $encoded];
                    }
                }

                if (count($deliveryPayload) < $item['quantity']) {
                    $lineDelivered = false;
                }
            } elseif ($productData['delivery_mode'] === 'instant') {
                $note = 'Teslim edildi: ' . $productData['name'] . ' x' . $item['quantity'];
                $deliveryPayload[] = ['note' => base64_encode($note)];
            } else {
                $lineDelivered = false;
            }

            if ($deliveryPayload) {
                $deliveredPayloads[] = [
                    'product' => $productData,
                    'variant_name' => $variantName,
                    'items' => $deliveryPayload,
                ];
            }

            if (!$lineDelivered) {
                $allDelivered = false;
            }

            $itemStmt->execute([
                'order_id' => $orderId,
                'product_id' => $productData['id'],
                'variant_id' => $variantId,
                'qty' => $item['quantity'],
                'unit_price' => $unitPrice,
                'requires_input_value' => $item['input_value'] ?? null,
                'delivery_json' => $deliveryPayload ? json_encode($deliveryPayload, JSON_UNESCAPED_UNICODE) : null,
            ]);
        }

        $nextStatus = $allDelivered ? 'delivered' : 'processing';
        database()->prepare('UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id')
            ->execute(['status' => $nextStatus, 'id' => $orderId]);

        if ($coupon && $totals['discount'] > 0) {
            database()->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = :id')
                ->execute(['id' => $coupon['id']]);
        }

        if ($nextStatus === 'delivered' && !empty($_POST['email']) && !empty($deliveredPayloads)) {
            $mailer->send(
                $_POST['email'],
                'Sipariş Teslimatı #' . $orderId,
                'emails/order_delivery',
                [
                    'orderId' => $orderId,
                    'payloads' => $deliveredPayloads,
                    'total' => $total,
                ]
            );
        }

        audit('order_checkout', [
            'order_id' => $orderId,
            'total' => $total,
            'status' => $nextStatus,
            'coupon' => $coupon['code'] ?? null,
        ]);

        $_SESSION['cart'] = [];
        unset($_SESSION['coupon']);
        session_flash('success', 'Siparişiniz oluşturuldu.');

        return $this->redirect('/order/' . $orderId);
    }

    protected function calculateTotals(array $cart, ?array $coupon = null): array
    {
        $subtotal = array_reduce($cart, fn ($sum, $item) => $sum + (($item['product']['price'] ?? 0) * $item['quantity']), 0.0);

        $discount = 0.0;
        if ($coupon) {
            if ($subtotal < (float) $coupon['min_cart']) {
                $coupon = null;
            } else {
                if ($coupon['type'] === 'percent') {
                    $discount = $subtotal * ((float) $coupon['value'] / 100);
                } else {
                    $discount = (float) $coupon['value'];
                }
                if (!empty($coupon['max_discount'])) {
                    $discount = min($discount, (float) $coupon['max_discount']);
                }
                $discount = min($discount, $subtotal);
            }
        }

        $total = max(0.0, $subtotal - $discount);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ];
    }
}
