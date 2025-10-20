<section aria-labelledby="orders-title">
    <h1 id="orders-title">Siparişlerim</h1>
    <?php if (empty($orders)): ?>
        <p>Siparişiniz bulunmuyor.</p>
    <?php else: ?>
        <ul class="order-list">
            <?php foreach ($orders as $order): ?>
                <li>
                    <article>
                        <h2>Sipariş #<?= (int) $order['id'] ?></h2>
                        <p>Tutar: ₺<?= number_format($order['total_amount'], 2) ?></p>
                        <p>Durum: <?= sanitize($order['status']) ?></p>
                        <p>Tarih: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
