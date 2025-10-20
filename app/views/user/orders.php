<section aria-labelledby="orders-title">
    <h1 id="orders-title">Siparişlerim</h1>
    <?php if (!empty($activeOrder)): ?>
        <section class="order-detail" aria-labelledby="order-detail-title">
            <h2 id="order-detail-title">Seçili Sipariş #<?= (int) $activeOrder['id'] ?></h2>
            <p>Durum: <?= sanitize($activeOrder['status']) ?></p>
            <p>Tutar: ₺<?= number_format($activeOrder['total'], 2) ?></p>
            <?php if (!empty($activeOrderItems)): ?>
                <h3>Ürünler</h3>
                <ul>
                    <?php foreach ($activeOrderItems as $item): ?>
                        <li>
                            <?= sanitize($item['product_name'] ?? ('Ürün #' . $item['product_id'])) ?> x <?= (int) $item['qty'] ?> - ₺<?= number_format($item['unit_price'], 2) ?>
                            <?php if (!empty($item['delivery_json'])): ?>
                                <?php $deliveries = json_decode($item['delivery_json'], true) ?: []; ?>
                                <ul>
                                    <?php foreach ($deliveries as $delivery): ?>
                                        <?php if (!empty($delivery['code'])): ?>
                                            <li><code><?= sanitize($delivery['code']) ?></code></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>
    <?php if (empty($orders)): ?>
        <p>Siparişiniz bulunmuyor.</p>
    <?php else: ?>
        <ul class="order-list">
            <?php foreach ($orders as $order): ?>
                <li>
                    <article>
                        <h2>Sipariş #<?= (int) $order['id'] ?></h2>
                        <p>Tutar: ₺<?= number_format($order['total'], 2) ?></p>
                        <p>Durum: <?= sanitize($order['status']) ?></p>
                        <p>Tarih: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                        <a href="/order/<?= (int) $order['id'] ?>">Detay</a>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
