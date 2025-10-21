<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\StockCode;
use App\Models\Variant;

class CartController extends Controller
{
    public function index()
    {
        $cart = $_SESSION['cart'] ?? [];
        [$subtotal, $discount, $total] = $this->totals($cart);

        return $this->view('store/cart', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $_SESSION['coupon'] ?? null,
        ]);
    }

    public function add()
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/cart');
        }

        $productModel = new Product();
        $variantModel = new Variant();
        $product = $productModel->findBySlug($_POST['slug'] ?? '');
        if (!$product || !$product['is_active']) {
            session_flash('error', 'Ürün bulunamadı.');
            return $this->redirect('/');
        }

        $variant = null;
        if (!empty($_POST['variant_id'])) {
            $variant = $variantModel->findActive((int) $_POST['variant_id']);
            if (!$variant || (int) $variant['product_id'] !== (int) $product['id']) {
                session_flash('error', 'Seçilen varyant geçersiz.');
                return $this->redirect('/urun/' . $product['slug']);
            }
            $product['price'] = (float) $variant['price'];
            $product['variant_id'] = (int) $variant['id'];
            $product['variant_name'] = $variant['name'];
            $product['stock_visible'] = (int) $variant['stock_visible'];
        }

        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $minQty = (int) ($product['min_qty'] ?? 1);
        $maxQty = (int) ($product['max_qty'] ?? $quantity);
        if ($maxQty <= 0) {
            $maxQty = $quantity;
        }
        $stockModel = new StockCode();
        $availableStock = $stockModel->remainingCount((int) $product['id'], $product['variant_id'] ?? null);
        if ($product['delivery_mode'] === 'auto' && $availableStock <= 0) {
            session_flash('error', 'Üzgünüz, bu ürün için stok tükenmiş.');
            return $this->redirect('/urun/' . $product['slug']);
        }

        if ($availableStock > 0) {
            $maxQty = (int) min($maxQty, $availableStock);
        }
        $quantity = max($minQty, min($quantity, $maxQty));

        $inputValue = null;
        if (!empty($product['requires_input'])) {
            $inputValue = trim($_POST['inputs'][$product['id']] ?? '');
            if ($inputValue === '') {
                session_flash('error', 'Lütfen gerekli bilgiyi giriniz.');
                return $this->redirect('/urun/' . $product['slug']);
            }
        }

        $cartKey = $product['id'] . '-' . ($product['variant_id'] ?? 0);

        $_SESSION['cart'][$cartKey] = [
            'key' => $cartKey,
            'product' => $product,
            'quantity' => $quantity,
            'input_value' => $inputValue,
        ];

        session_flash('success', 'Ürün sepete eklendi.');
        return $this->redirect('/cart');
    }

    public function update()
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/cart');
        }

        $cart = $_SESSION['cart'] ?? [];
        $stockModel = new StockCode();
        foreach ($_POST['quantity'] ?? [] as $key => $qty) {
            if (!isset($cart[$key])) {
                continue;
            }
            $qty = max(1, (int) $qty);
            $product = $cart[$key]['product'];

            $maxQty = (int) ($product['max_qty'] ?? $qty);
            if ($maxQty <= 0) {
                $maxQty = $qty;
            }

            $available = $stockModel->remainingCount((int) $product['id'], $product['variant_id'] ?? null);
            if ($available > 0) {
                $maxQty = min($maxQty, $available);
            }

            $minQty = (int) ($product['min_qty'] ?? 1);
            $cart[$key]['quantity'] = max($minQty, min($maxQty, $qty));
        }

        $_SESSION['cart'] = $cart;
        [$subtotal] = $this->totals($cart, null, false);
        if (isset($_SESSION['coupon']) && $subtotal < (float) $_SESSION['coupon']['min_cart']) {
            unset($_SESSION['coupon']);
            session_flash('error', 'Kupon koşulları karşılanmadığı için kaldırıldı.');
        } else {
            session_flash('success', 'Sepet güncellendi.');
        }

        return $this->redirect('/cart');
    }

    public function applyCoupon()
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/cart');
        }

        $code = strtoupper(trim($_POST['coupon'] ?? ''));
        if ($code === '') {
            unset($_SESSION['coupon']);
            session_flash('success', 'Kupon temizlendi.');
            return $this->redirect('/cart');
        }

        $couponModel = new Coupon();
        $coupon = $couponModel->findValid($code);

        if (!$coupon) {
            session_flash('error', 'Kupon bulunamadı veya geçersiz.');
            return $this->redirect('/cart');
        }

        $cart = $_SESSION['cart'] ?? [];
        [$subtotal] = $this->totals($cart, null, false);

        if ($subtotal < (float) $coupon['min_cart']) {
            session_flash('error', 'Kupon için yeterli sepet tutarı yok.');
            return $this->redirect('/cart');
        }

        $_SESSION['coupon'] = $coupon;
        session_flash('success', 'Kupon uygulandı.');

        return $this->redirect('/cart');
    }

    public function remove(string $key)
    {
        unset($_SESSION['cart'][$key]);
        [$subtotal] = $this->totals($_SESSION['cart'] ?? [], null, false);
        if (isset($_SESSION['coupon']) && $subtotal < (float) $_SESSION['coupon']['min_cart']) {
            unset($_SESSION['coupon']);
            session_flash('error', 'Kupon koşulları karşılanmadığı için kaldırıldı.');
        } else {
            session_flash('success', 'Ürün sepetten çıkarıldı.');
        }
        return $this->redirect('/cart');
    }

    protected function totals(array $cart, ?array $coupon = null, bool $includeCoupon = true): array
    {
        $subtotal = array_reduce($cart, fn ($sum, $item) => $sum + (($item['product']['price'] ?? 0) * $item['quantity']), 0.0);
        $coupon = $coupon ?? ($_SESSION['coupon'] ?? null);

        if (!$includeCoupon || !$coupon) {
            return [$subtotal, 0.0, $subtotal];
        }

        if ($subtotal < (float) $coupon['min_cart']) {
            return [$subtotal, 0.0, $subtotal];
        }

        $discount = 0.0;
        if ($coupon['type'] === 'percent') {
            $discount = ($subtotal * ((float) $coupon['value'] / 100));
        } else {
            $discount = (float) $coupon['value'];
        }

        if (!empty($coupon['max_discount'])) {
            $discount = min($discount, (float) $coupon['max_discount']);
        }

        $discount = min($discount, $subtotal);
        $total = max(0.0, $subtotal - $discount);

        return [$subtotal, $discount, $total];
    }
}
