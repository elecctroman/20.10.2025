<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Mailer;
use App\Models\Order;
use App\Models\StockCode;

class DeliveriesController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();

        $orderModel = new Order();
        $pending = $orderModel->allPendingManual();

        return $this->view('admin/deliveries_pending', [
            'orders' => $pending,
        ], 'admin');
    }

    public function assign(int $itemId)
    {
        Auth::requireAdmin();

        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/admin/deliveries');
        }

        $codesInput = trim($_POST['codes'] ?? '');
        if ($codesInput === '') {
            session_flash('error', 'Teslim edilecek kodları giriniz.');
            return $this->redirect('/admin/deliveries');
        }

        $itemStmt = database()->prepare(
            'SELECT oi.*, p.name AS product_name, p.delivery_mode, v.name AS variant_name, o.user_id, o.id AS order_id, o.total, u.email
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             LEFT JOIN users u ON u.id = o.user_id
             LEFT JOIN products p ON p.id = oi.product_id
             LEFT JOIN variants v ON v.id = oi.variant_id
             WHERE oi.id = :id LIMIT 1'
        );
        $itemStmt->execute(['id' => $itemId]);
        $item = $itemStmt->fetch();

        if (!$item) {
            session_flash('error', 'Sipariş öğesi bulunamadı.');
            return $this->redirect('/admin/deliveries');
        }

        $codes = array_filter(array_map('trim', preg_split('/\r?\n/', $codesInput)), fn ($code) => $code !== '');
        $note = trim($_POST['note'] ?? '');

        if (empty($codes)) {
            session_flash('error', 'Geçerli kod bulunamadı.');
            return $this->redirect('/admin/deliveries');
        }

        $stockModel = new StockCode();
        $delivered = $stockModel->createAndAssign((int) $item['product_id'], $item['variant_id'] ? (int) $item['variant_id'] : null, $codes, (int) $item['order_id']);

        $existing = json_decode($item['delivery_json'] ?? '', true) ?? [];
        $payload = array_merge($existing, array_map(fn ($entry) => ['code' => base64_encode($entry['code'])], $delivered));

        if ($note !== '') {
            $payload[] = ['note' => base64_encode($note)];
        }

        database()->prepare('UPDATE order_items SET delivery_json = :delivery_json WHERE id = :id')
            ->execute([
                'delivery_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'id' => $item['id'],
            ]);

        $itemsStmt = database()->prepare('SELECT qty, delivery_json FROM order_items WHERE order_id = :order_id');
        $itemsStmt->execute(['order_id' => $item['order_id']]);
        $remaining = 0;
        foreach ($itemsStmt->fetchAll() as $row) {
            $deliveries = json_decode($row['delivery_json'] ?? '', true) ?? [];
            $deliveredCount = count(array_filter($deliveries, fn ($entry) => !empty($entry['code'])));
            if ($deliveredCount < (int) $row['qty']) {
                $remaining++;
            }
        }

        $mailer = new Mailer();
        if (!empty($item['email'])) {
            $mailer->send($item['email'], 'Sipariş Teslimatı #' . $item['order_id'], 'emails/order_delivery', [
                'orderId' => $item['order_id'],
                'payloads' => [[
                    'product' => ['name' => $item['product_name']],
                    'variant_name' => $item['variant_name'],
                    'items' => $payload,
                ]],
                'total' => $item['total'],
            ]);
        }

        if ($remaining === 0) {
            database()->prepare('UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id')
                ->execute(['status' => 'delivered', 'id' => $item['order_id']]);
        }

        audit('manual_delivery', [
            'order_id' => $item['order_id'],
            'item_id' => $item['id'],
            'count' => count($codes),
        ]);

        session_flash('success', 'Teslimat tamamlandı.');
        return $this->redirect('/admin/deliveries');
    }
}
