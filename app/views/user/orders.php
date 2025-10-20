<section class="section-shell" aria-labelledby="orders-title">
    <?php include __DIR__ . '/partials/tabs.php'; ?>
    <header class="section-title">
        <h1 id="orders-title">Siparişlerim</h1>
        <span>Geçmiş sipariş ve teslimatlarınız</span>
    </header>
    <?php if (empty($orders)): ?>
        <p>Henüz sipariş oluşturmadınız.</p>
    <?php else: ?>
        <div class="order-board">
            <?php foreach ($orders as $order): ?>
                <?php
                $statusMap = [
                    'pending' => 15,
                    'paid' => 40,
                    'processing' => 65,
                    'delivered' => 100,
                    'failed' => 100,
                    'refunded' => 100,
                    'cancelled' => 100,
                ];
                $progress = $statusMap[$order['status']] ?? 50;
                ?>
                <article class="order-card">
                    <h2>#<?= (int) $order['id'] ?> • <?= strtoupper($order['status']) ?></h2>
                    <p>Tutar: ₺<?= number_format($order['total'], 2) ?> — <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                    <div class="progress-bar"><span style="width: <?= $progress ?>%"></span></div>
                    <a class="button secondary" href="/order/<?= (int) $order['id'] ?>">Detaylar</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
