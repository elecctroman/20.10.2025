<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function show(int $orderId)
    {
        Auth::check();
        $orderModel = new Order();
        $orders = $orderModel->byUser(Auth::user()['id']);
        $order = array_values(array_filter($orders, fn ($o) => (int) $o['id'] === $orderId))[0] ?? null;

        if (!$order) {
            http_response_code(404);
            return $this->view('errors/404');
        }

        return $this->view('user/orders', ['orders' => $orders, 'activeOrder' => $order], 'store');
    }
}
