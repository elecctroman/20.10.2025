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

        $items = array_map(function (array $item) {
            $deliveries = json_decode($item['delivery_json'] ?? '', true) ?? [];
            $masked = array_map(function ($entry) {
                if (!empty($entry['code'])) {
                    $decoded = base64_decode($entry['code'], true);
                    if ($decoded === false) {
                        $decoded = '';
                    }
                    $length = mb_strlen($decoded);
                    if ($length <= 6) {
                        $visible = str_repeat('•', max(4, $length));
                    } else {
                        $visible = mb_substr($decoded, 0, 4) . str_repeat('•', max(0, $length - 8)) . mb_substr($decoded, -4);
                    }
                    return ['type' => 'code', 'value' => $visible, 'raw' => $entry['code']];
                }
                if (!empty($entry['note'])) {
                    $note = base64_decode($entry['note'], true);
                    return ['type' => 'note', 'value' => $note !== false ? $note : ''];
                }
                return ['type' => 'note', 'value' => ''];
            }, $deliveries);

            $item['deliveries'] = $masked;
            return $item;
        }, $orderModel->items($orderId));

        return $this->view('user/orders', [
            'orders' => $orders,
            'activeOrder' => $order,
            'activeOrderItems' => $items,
        ], 'store');
    }
}
