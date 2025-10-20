<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;

class OrdersController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();
        $stmt = database()->query('SELECT * FROM orders ORDER BY created_at DESC');
        return $this->view('admin/orders', ['orders' => $stmt->fetchAll()], 'admin');
    }
}
