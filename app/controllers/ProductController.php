<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Category;
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

        $related = [];
        if (!empty($product['category_id'])) {
            $related = $productModel->related((int) $product['category_id'], (int) $product['id']);
        }

        $category = null;
        if (!empty($product['category_id'])) {
            $categoryModel = new Category();
            $category = $categoryModel->find((int) $product['category_id']);
        }

        return $this->view('store/product', [
            'product' => $product,
            'variants' => $variantModel->byProduct((int) $product['id']),
            'stock' => $stockModel->remainingCount((int) $product['id'], $product['variant_id'] ?? null),
            'related' => $related,
            'category' => $category,
        ]);
    }
}
