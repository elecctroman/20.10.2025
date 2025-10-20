<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Variant;
use App\Models\StockCode;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $productModel = new Product();
        $variantModel = new Variant();
        $stockModel = new StockCode();

        $product = $productModel->findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            return $this->view('errors/404', [], 'store');
        }

        return $this->view('store/product', [
            'product' => $product,
            'variants' => $variantModel->byProduct((int) $product['id']),
            'stock' => $stockModel->remainingCount((int) $product['id']),
        ]);
    }
}
