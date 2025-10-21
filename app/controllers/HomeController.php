<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Blog;

class HomeController extends Controller
{
    public function index()
    {
        $productModel = new Product();
        $categoryModel = new Category();
        $blogModel = new Blog();

        return $this->view('store/home', [
            'products' => $productModel->allActive(),
            'categories' => $categoryModel->all(),
            'posts' => $blogModel->allPublished(),
        ]);
    }
}
