<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function show(int $orderId)
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $orderModel = new Order();
        $orders = $orderModel->byUser(Auth::user()['id']);
        $order = array_values(array_filter($orders, fn ($o) => (int) $o['id'] === $orderId))[0] ?? null;

        if (!$order) {
            http_response_code(404);
            return $this->view('errors/404');
        }

        $itemsStmt = database()->prepare('SELECT oi.*, p.name AS product_name FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id');
        $itemsStmt->execute(['order_id' => $orderId]);

        return $this->view('user/orders', [
            'orders' => $orders,
            'activeOrder' => $order,
            'activeOrderItems' => $itemsStmt->fetchAll(),
        ], 'store');
    }
}
