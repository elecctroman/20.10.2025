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
        $db = database();

        $recentOrders = $db->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 5')->fetchAll();

        $today = $db->query("SELECT COUNT(*) AS count, COALESCE(SUM(total),0) AS total FROM orders WHERE DATE(created_at) = CURDATE()")
            ->fetch();
        $yesterday = $db->query("SELECT COUNT(*) AS count, COALESCE(SUM(total),0) AS total FROM orders WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")
            ->fetch();
        $week = $db->query("SELECT COUNT(*) AS count, COALESCE(SUM(total),0) AS total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")
            ->fetch();

        $trendStmt = $db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-01') AS month, SUM(total) AS total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) GROUP BY month ORDER BY month");
        $trendStmt->execute();
        $trendRaw = $trendStmt->fetchAll();

        $months = [];
        $period = new \DatePeriod(new \DateTime('first day of this month -11 months'), new \DateInterval('P1M'), 12);
        foreach ($period as $date) {
            $months[$date->format('Y-m-01')] = 0.0;
        }
        foreach ($trendRaw as $row) {
            $months[$row['month']] = (float) $row['total'];
        }

        $deliveriesStmt = $db->query("SELECT o.id AS order_id, p.name AS product_name, v.name AS variant_name, oi.qty, oi.requires_input_value, scRemaining.remaining
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN variants v ON v.id = oi.variant_id
            LEFT JOIN (
                SELECT product_id, COUNT(*) AS remaining FROM stock_codes WHERE is_used = 0 GROUP BY product_id
            ) scRemaining ON scRemaining.product_id = p.id
            WHERE o.status IN ('paid','processing')");
        $pendingDeliveries = $deliveriesStmt->fetchAll();

        $topStmt = $db->prepare("SELECT p.name, SUM(oi.qty) AS quantity FROM order_items oi JOIN orders o ON o.id = oi.order_id JOIN products p ON p.id = oi.product_id WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY p.name ORDER BY quantity DESC LIMIT 5");
        $topStmt->execute();
        $topSellers = $topStmt->fetchAll();

        return $this->view('admin/dashboard', [
            'orders' => $recentOrders,
            'productCount' => count($productModel->allActive()),
            'user' => Auth::user(),
            'today' => $today,
            'yesterday' => $yesterday,
            'week' => $week,
            'trend' => $months,
            'pendingDeliveries' => $pendingDeliveries,
            'topSellers' => $topSellers,
        ], 'admin');
    }
}
