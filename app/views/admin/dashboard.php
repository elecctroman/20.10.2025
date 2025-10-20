<section class="dashboard-grid" aria-labelledby="admin-dashboard-title">
    <header class="section-title">
        <h1 id="admin-dashboard-title">Gösterge Paneli</h1>
        <span>Son sipariş hareketleri ve teslimatlar</span>
    </header>
    <div class="dashboard-kpis">
        <article class="kpi-card">
            <span>Bugünkü Ciro</span>
            <strong>₺<?= number_format((float)($today['total'] ?? 0), 2) ?></strong>
            <small><?= (int)($today['count'] ?? 0) ?> sipariş</small>
        </article>
        <article class="kpi-card">
            <span>Dünkü Ciro</span>
            <strong>₺<?= number_format((float)($yesterday['total'] ?? 0), 2) ?></strong>
            <small><?= (int)($yesterday['count'] ?? 0) ?> sipariş</small>
        </article>
        <article class="kpi-card">
            <span>7 Günlük Hacim</span>
            <strong>₺<?= number_format((float)($week['total'] ?? 0), 2) ?></strong>
            <small><?= (int)($week['count'] ?? 0) ?> sipariş</small>
        </article>
        <article class="kpi-card">
            <span>Aktif Ürün</span>
            <strong><?= (int) $productCount ?></strong>
            <small>Katalog</small>
        </article>
    </div>
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
                        <th>Sipariş</th>
                        <th>Ürün</th>
                        <th>Varyant</th>
                        <th>Adet</th>
                        <th>Kalan</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pendingDeliveries as $delivery): ?>
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
                <ul>
                    <?php foreach ($topSellers as $seller): ?>
                        <li>
                            <?= sanitize($seller['name']) ?>
                            <div class="progress-small"><span style="width: <?= min(100, ((int) $seller['quantity']) * 10) ?>%"></span></div>
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
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= (int) $order['id'] ?></td>
                        <td>₺<?= number_format($order['total'], 2) ?></td>
                        <td><span class="badge"><?= strtoupper($order['status']) ?></span></td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</section>
