<?php
$progressMap = [
    'pending' => ['label' => 'Beklemede', 'percent' => 25],
    'paid' => ['label' => 'Ödeme Alındı', 'percent' => 40],
    'processing' => ['label' => 'Teslimat Hazırlanıyor', 'percent' => 65],
    'delivered' => ['label' => 'Teslim Edildi', 'percent' => 100],
    'failed' => ['label' => 'Başarısız', 'percent' => 5],
    'refunded' => ['label' => 'İade', 'percent' => 5],
    'cancelled' => ['label' => 'İptal', 'percent' => 5],
];

$statusBadge = function (string $status) use ($progressMap): array {
    $status = strtolower($status);
    return $progressMap[$status] ?? ['label' => ucfirst($status), 'percent' => 25];
};

$formatCurrency = static fn (float $value): string => '₺' . number_format($value, 2);

$distributionTotal = array_sum($distribution);
?>
<section class="dashboard-grid" aria-labelledby="admin-dashboard-title">
    <header class="section-title">
        <h1 id="admin-dashboard-title">Gösterge Paneli</h1>
        <span>Son sipariş hareketleri ve teslimatlar</span>
    </header>
    <div class="dashboard-kpis">
        <article class="kpi-card">
            <span>Bugünkü Ciro</span>
            <strong><?= $formatCurrency((float)($today['total'] ?? 0)) ?></strong>
            <small><?= (int)($today['count'] ?? 0) ?> sipariş · Ort. <?= $formatCurrency((float)($today['average'] ?? 0)) ?></small>
        </article>
        <article class="kpi-card">
            <span>Dünkü Performans</span>
            <strong><?= $formatCurrency((float)($yesterday['total'] ?? 0)) ?></strong>
            <small><?= (int)($yesterday['count'] ?? 0) ?> sipariş · Ort. <?= $formatCurrency((float)($yesterday['average'] ?? 0)) ?></small>
        </article>
        <article class="kpi-card">
            <span>30 Günlük Hacim</span>
            <strong><?= $formatCurrency((float)($shortTerm['total'] ?? 0)) ?></strong>
            <small><?= (int)($shortTerm['count'] ?? 0) ?> sipariş · Ort. <?= $formatCurrency((float)($shortTerm['average'] ?? 0)) ?></small>
        </article>
        <article class="kpi-card">
            <span>Aktif Ürün</span>
            <strong><?= (int) $productCount ?></strong>
            <small>Katalog</small>
        </article>
    </div>
    <section class="panel-section" aria-label="Teslimat durumu dağılımı">
        <h2>Teslimat Durumu Dağılımı</h2>
        <ul class="status-distribution" role="list">
            <?php foreach ([
                'waiting' => 'Beklemede',
                'stock' => 'Stok Bekleniyor',
                'done' => 'Tamamlandı',
                'failed' => 'Başarısız',
            ] as $key => $label): $count = $distribution[$key] ?? 0; $percent = $distributionTotal > 0 ? round($count / $distributionTotal * 100) : 0; ?>
                <li>
                    <span><?= $label ?></span>
                    <div class="progress-small" aria-hidden="true"><span style="width: <?= $percent ?>%"></span></div>
                    <small><?= $count ?> sipariş · %<?= $percent ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="panel-section" aria-label="12 aylık trend">
        <h2>12 Aylık Ciro Trend</h2>
        <canvas class="trend-chart" id="trend-chart" data-trend='<?= json_encode(array_values($trend), JSON_UNESCAPED_UNICODE) ?>'></canvas>
    </section>
    <div class="flex-split">
        <section class="panel-section" aria-labelledby="pending-heading">
            <h2 id="pending-heading">Eksik Teslimatlar</h2>
            <?php if (empty($pendingDeliveries)): ?>
                <p>Tüm siparişler teslim edildi.</p>
            <?php else: ?>
                <table class="table-list">
                    <thead>
                    <tr>
                        <th scope="col">Sipariş</th>
                        <th scope="col">Ürün</th>
                        <th scope="col">Varyant</th>
                        <th scope="col">Adet</th>
                        <th scope="col">Kalan</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (array_slice($pendingDeliveries, 0, 8) as $delivery): ?>
                        <tr>
                            <td>#<?= (int) $delivery['order_id'] ?></td>
                            <td><?= sanitize($delivery['product_name']) ?></td>
                            <td><?= sanitize($delivery['variant_name'] ?? 'Standart') ?></td>
                            <td><?= (int) $delivery['qty'] ?></td>
                            <td><?= (int) ($delivery['remaining'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
        <section class="panel-section" aria-labelledby="top-heading">
            <h2 id="top-heading">En Çok Satanlar (30 Gün)</h2>
            <?php if (empty($topSellers)): ?>
                <p>Yeterli veri yok.</p>
            <?php else: ?>
                <ul class="top-sellers" role="list">
                    <?php $maxQty = max(array_map(fn ($seller) => (int) $seller['quantity'], $topSellers)); ?>
                    <?php foreach ($topSellers as $seller): $percent = $maxQty > 0 ? min(100, round(((int) $seller['quantity'] / $maxQty) * 100)) : 0; ?>
                        <li>
                            <div>
                                <strong><?= sanitize($seller['name']) ?></strong>
                                <small><?= (int) $seller['quantity'] ?> adet · <?= $formatCurrency((float) $seller['revenue']) ?></small>
                            </div>
                            <div class="progress-small" aria-hidden="true"><span style="width: <?= $percent ?>%"></span></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
    <section class="panel-section" aria-labelledby="recent-orders">
        <h2 id="recent-orders">Son 5 Sipariş</h2>
        <?php if (empty($orders)): ?>
            <p>Henüz sipariş bulunmuyor.</p>
        <?php else: ?>
            <table class="table-list">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Müşteri</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= (int) $order['id'] ?></td>
                        <td>
                            <?php if (!empty($order['email'])): ?>
                                <?= sanitize($order['email']) ?>
                            <?php else: ?>
                                Misafir
                            <?php endif; ?>
                        </td>
                        <td><?= $formatCurrency((float) $order['total']) ?></td>
                        <?php $meta = $statusBadge($order['status']); ?>
                        <td>
                            <span class="badge"><?= sanitize(strtoupper($order['status'])) ?></span>
                            <div class="progress-small" aria-hidden="true"><span style="width: <?= (int) $meta['percent'] ?>%"></span></div>
                            <small><?= sanitize($meta['label']) ?></small>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</section>
