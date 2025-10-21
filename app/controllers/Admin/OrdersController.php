<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Mailer;
use App\Models\Order;

class OrdersController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();
        $orderModel = new Order();

        $rawFilters = [
            'status' => $_GET['status'] ?? '',
            'gateway' => $_GET['gateway'] ?? '',
            'from' => $_GET['from'] ?? '',
            'to' => $_GET['to'] ?? '',
            'q' => trim((string) ($_GET['q'] ?? '')),
        ];

        $filters = $rawFilters;

        if (!empty($rawFilters['from'])) {
            try {
                $from = new \DateTime($rawFilters['from']);
                $filters['from'] = $from->format('Y-m-d 00:00:00');
            } catch (\Exception $e) {
                $filters['from'] = null;
            }
        }

        if (!empty($rawFilters['to'])) {
            try {
                $to = new \DateTime($rawFilters['to']);
                $filters['to'] = $to->format('Y-m-d 23:59:59');
            } catch (\Exception $e) {
                $filters['to'] = null;
            }
        }

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $result = $orderModel->adminList($filters, $page, 25);

        return $this->view('admin/orders', [
            'orders' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => $rawFilters,
            'statuses' => ['pending','paid','processing','delivered','failed','refunded','cancelled'],
            'gateways' => array_keys(config('payment.gateways')),
        ], 'admin');
    }

    public function show(int $orderId)
    {
        Auth::requireAdmin();
        $orderModel = new Order();
        $order = $orderModel->detail($orderId);

        if (!$order) {
            session_flash('error', 'Sipariş bulunamadı.');
            return $this->redirect('/admin/orders');
        }

        return $this->view('admin/order_detail', [
            'order' => $order,
            'payloads' => $orderModel->deliveryPayloads($order['items']),
            'statuses' => ['pending','paid','processing','delivered','failed','refunded','cancelled'],
        ], 'admin');
    }

    public function updateStatus(int $orderId)
    {
        Auth::requireAdmin();

        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/admin/orders/' . $orderId);
        }

        $status = $_POST['status'] ?? '';
        $note = trim($_POST['admin_note'] ?? '');

        $allowed = ['pending','paid','processing','delivered','failed','refunded','cancelled'];
        if (!in_array($status, $allowed, true)) {
            session_flash('error', 'Geçersiz sipariş durumu.');
            return $this->redirect('/admin/orders/' . $orderId);
        }

        $orderModel = new Order();
        if ($orderModel->updateStatus($orderId, $status, $note)) {
            audit('order_status_update', ['order_id' => $orderId, 'status' => $status]);
            session_flash('success', 'Sipariş durumu güncellendi.');
        } else {
            session_flash('error', 'Sipariş durumu güncellenemedi.');
        }

        return $this->redirect('/admin/orders/' . $orderId);
    }

    public function resend(int $orderId)
    {
        Auth::requireAdmin();

        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/admin/orders/' . $orderId);
        }

        $orderModel = new Order();
        $order = $orderModel->detail($orderId);

        if (!$order || empty($order['email'])) {
            session_flash('error', 'Teslimat e-postası için müşteri e-posta adresi bulunamadı.');
            return $this->redirect('/admin/orders/' . $orderId);
        }

        $payloads = $orderModel->deliveryPayloads($order['items']);
        if (empty($payloads)) {
            session_flash('error', 'Teslim edilecek kayıt bulunamadı.');
            return $this->redirect('/admin/orders/' . $orderId);
        }

        $mailer = new Mailer();
        $mailer->send($order['email'], 'Sipariş Teslimatı #' . $orderId, 'emails/order_delivery', [
            'orderId' => $orderId,
            'payloads' => $payloads,
            'total' => $order['total'],
        ]);

        audit('order_delivery_resend', ['order_id' => $orderId]);
        session_flash('success', 'Teslimat e-postası yeniden gönderildi.');

        return $this->redirect('/admin/orders/' . $orderId);
    }
}
