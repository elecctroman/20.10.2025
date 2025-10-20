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

        $page = max(1, (int) ($_GET['page'] ?? 1));

        $filters = [
            'delivery' => $_GET['delivery'] ?? null,
            'variant' => trim($_GET['variant'] ?? ''),
            'price_min' => $_GET['price_min'] ?? null,
            'price_max' => $_GET['price_max'] ?? null,
            'sort' => $_GET['sort'] ?? 'featured',
        ];

        if (!in_array($filters['sort'], ['featured', 'price_asc', 'price_desc', 'newest'], true)) {
            $filters['sort'] = 'featured';
        }

        if (!is_numeric($filters['price_min'])) {
            $filters['price_min'] = null;
        }
        if (!is_numeric($filters['price_max'])) {
            $filters['price_max'] = null;
        }

        $result = $productModel->filterByCategory((int) $category['id'], $filters, $page);
        $variants = $productModel->variantNamesByCategory((int) $category['id']);
        $priceRange = $productModel->priceRangeByCategory((int) $category['id']);

        return $this->view('store/category', [
            'category' => $category,
            'products' => $result['items'],
            'filters' => $filters,
            'pagination' => $result,
            'variantOptions' => $variants,
            'priceRange' => $priceRange,
        ]);
    }
}
