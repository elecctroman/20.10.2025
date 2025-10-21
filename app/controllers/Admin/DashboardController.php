<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();
        $productModel = new Product();
        $orderModel = new Order();
        $db = database();

        $timezone = new \DateTimeZone(config('app.timezone'));
        $todayStart = (new \DateTimeImmutable('today', $timezone))->format('Y-m-d H:i:s');
        $tomorrowStart = (new \DateTimeImmutable('tomorrow', $timezone))->format('Y-m-d H:i:s');
        $yesterdayStart = (new \DateTimeImmutable('yesterday', $timezone))->format('Y-m-d H:i:s');
        $shortTermStart = (new \DateTimeImmutable('-30 days', $timezone))->format('Y-m-d H:i:s');

        $today = $orderModel->aggregate($todayStart, $tomorrowStart);
        $yesterday = $orderModel->aggregate($yesterdayStart, $todayStart);
        $shortTerm = $orderModel->aggregate($shortTermStart, $tomorrowStart);

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

        $pendingManual = $orderModel->allPendingManual();
        $pendingDeliveries = [];
        foreach ($pendingManual as $order) {
            foreach ($order['items'] as $item) {
                $pendingDeliveries[] = [
                    'order_id' => $order['id'],
                    'product_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'],
                    'qty' => $item['qty'],
                    'remaining' => $item['remaining'],
                ];
            }
        }

        $distributionRaw = $orderModel->statusBreakdown($shortTermStart, $tomorrowStart);
        $distribution = [
            'waiting' => 0,
            'stock' => 0,
            'done' => 0,
            'failed' => 0,
        ];
        foreach ($distributionRaw as $status => $count) {
            $bucket = match ($status) {
                'pending' => 'waiting',
                'paid', 'processing' => 'stock',
                'delivered' => 'done',
                'failed', 'cancelled', 'refunded' => 'failed',
                default => 'waiting',
            };
            $distribution[$bucket] += $count;
        }

        $topSellers = $orderModel->topSellersSince($shortTermStart);
        $recentOrders = $orderModel->recentWithUser(5);

        return $this->view('admin/dashboard', [
            'orders' => $recentOrders,
            'productCount' => count($productModel->allActive()),
            'user' => Auth::user(),
            'today' => $today,
            'yesterday' => $yesterday,
            'shortTerm' => $shortTerm,
            'trend' => $months,
            'pendingDeliveries' => $pendingDeliveries,
            'topSellers' => $topSellers,
            'distribution' => $distribution,
        ], 'admin');
    }
}
