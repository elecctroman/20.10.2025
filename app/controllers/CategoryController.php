<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $categoryModel = new Category();
        $productModel = new Product();

        $category = $categoryModel->findBySlug($slug);
        if (!$category) {
            http_response_code(404);
            return $this->view('errors/404', [], 'store');
        }

        $products = $productModel->byCategory((int) $category['id']);

        return $this->view('store/category', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
