<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();
        $productModel = new Product();

        $recentOrders = database()->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 5')->fetchAll();

        return $this->view('admin/dashboard', [
            'orders' => $recentOrders,
            'productCount' => count($productModel->allActive()),
            'user' => Auth::user(),
        ], 'admin');
    }
}
