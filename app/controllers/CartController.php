<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
use App\Models\Product;

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
        $product = $productModel->findBySlug($_POST['slug'] ?? '');
        if (!$product) {
            session_flash('error', 'Ürün bulunamadı.');
            return $this->redirect('/');
        }

        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $_SESSION['cart'][$product['id']] = [
            'product' => $product,
            'quantity' => $quantity,
        ];

        session_flash('success', 'Ürün sepete eklendi.');
        return $this->redirect('/cart');
    }

    public function remove(int $productId)
    {
        unset($_SESSION['cart'][$productId]);
        session_flash('success', 'Ürün sepetten çıkarıldı.');
        return $this->redirect('/cart');
    }
}
