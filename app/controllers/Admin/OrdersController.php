<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;

class OrdersController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();
        $orderModel = new Order();
        $stmt = database()->query('SELECT * FROM orders ORDER BY created_at DESC');
        return $this->view('admin/orders', ['orders' => $stmt->fetchAll()], 'admin');
    }
}
