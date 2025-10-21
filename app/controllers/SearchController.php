<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class SearchController extends Controller
{
    public function index()
    {
        $query = trim($_GET['q'] ?? '');
        $results = [];

        if ($query !== '') {
            $productModel = new Product();
            $results = $productModel->search($query);
        }

        return $this->view('store/search', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
