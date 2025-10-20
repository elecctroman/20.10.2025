<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
use App\Models\Product;
use App\Models\Variant;

class CartController extends Controller
{
    public function index()
    {
        $cart = $_SESSION['cart'] ?? [];
        return $this->view('store/cart', ['cart' => $cart]);
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
        if (!empty($product['stock_visible'])) {
            $maxQty = (int) min($maxQty, $product['stock_visible']);
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

    public function remove(string $key)
    {
        unset($_SESSION['cart'][$key]);
        session_flash('success', 'Ürün sepetten çıkarıldı.');
        return $this->redirect('/cart');
    }
}
