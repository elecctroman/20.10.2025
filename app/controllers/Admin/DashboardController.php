<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();
        $orderModel = new Order();
        $productModel = new Product();
        $userModel = new User();

        return $this->view('admin/dashboard', [
            'orders' => $orderModel->byUser(Auth::user()['id']),
            'productCount' => count($productModel->allActive()),
            'user' => Auth::user(),
        ], 'admin');
    }
}
