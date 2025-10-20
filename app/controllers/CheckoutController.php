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

        $total = array_reduce($cart, fn ($sum, $item) => $sum + ($item['product']['price'] * $item['quantity']), 0);

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
            'total_amount' => $total,
            'status' => 'paid',
            'payment_method' => $_POST['payment_method'],
        ]);

        $stockModel = new StockCode();
        foreach ($cart as $item) {
            database()->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, created_at, updated_at) VALUES (:order_id, :product_id, :quantity, :price, NOW(), NOW())')
                ->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product']['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['product']['price'],
                ]);

            if (!empty($item['product']['auto_delivery'])) {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $code = $stockModel->pull((int) $item['product']['id']);
                    if ($code) {
                        database()->prepare('UPDATE order_items SET delivered_code = :code WHERE order_id = :order_id AND product_id = :product_id AND delivered_code IS NULL LIMIT 1')
                            ->execute([
                                'code' => $code['code'],
                                'order_id' => $orderId,
                                'product_id' => $item['product']['id'],
                            ]);
                    }
                }
            }
        }

        $_SESSION['cart'] = [];
        session_flash('success', 'Siparişiniz oluşturuldu.');

        return $this->redirect('/order/' . $orderId);
    }
}
