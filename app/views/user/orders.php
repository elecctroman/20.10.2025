<?php
$statusLabels = [
    'pending' => 'Ödeme Bekleniyor',
    'paid' => 'Ödeme Alındı',
    'processing' => 'Hazırlanıyor',
    'delivered' => 'Teslim Edildi',
    'failed' => 'Başarısız',
    'refunded' => 'İade Edildi',
    'cancelled' => 'İptal Edildi',
];
$statusStyles = [
    'pending' => 'warning',
    'paid' => 'info',
    'processing' => 'info',
    'delivered' => 'success',
    'failed' => 'error',
    'refunded' => 'secondary',
    'cancelled' => 'secondary',
];
?>
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
                $conversions = convert_price_multi($order['total'] ?? 0);
                ?>
                <article class="order-card">
                    <div class="order-card-head">
                        <h2>#<?= (int) $order['id'] ?></h2>
                        <span class="badge <?= $statusStyles[$order['status']] ?? 'secondary' ?>"><?= $statusLabels[$order['status']] ?? strtoupper($order['status']) ?></span>
                    </div>
                    <p class="order-meta">Tutar: ₺<?= number_format($order['total'], 2) ?><?php if (isset($conversions['USD'])): ?> • $<?= number_format($conversions['USD'], 2) ?><?php endif; ?><?php if (isset($conversions['EUR'])): ?> • €<?= number_format($conversions['EUR'], 2) ?><?php endif; ?> — <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                    <div class="progress-bar"><span style="width: <?= $progress ?>%"></span></div>
                    <a class="button secondary" href="/order/<?= (int) $order['id'] ?>">Detaylar</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($activeOrder)): ?>
        <section class="order-detail" aria-labelledby="order-detail-title">
            <h2 id="order-detail-title">Sipariş #<?= (int) $activeOrder['id'] ?> Detayı</h2>
            <?php $detailPrices = convert_price_multi($activeOrder['total'] ?? 0); ?>
            <p>Durum: <span class="badge <?= $statusStyles[$activeOrder['status']] ?? 'secondary' ?>"><?= $statusLabels[$activeOrder['status']] ?? strtoupper($activeOrder['status']) ?></span> — ₺<?= number_format($activeOrder['total'], 2) ?><?php if (isset($detailPrices['USD'])): ?> • $<?= number_format($detailPrices['USD'], 2) ?><?php endif; ?><?php if (isset($detailPrices['EUR'])): ?> • €<?= number_format($detailPrices['EUR'], 2) ?><?php endif; ?></p>
            <table>
                <thead>
                <tr>
                    <th scope="col">Ürün</th>
                    <th scope="col">Adet</th>
                    <th scope="col">Teslimatlar</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($activeOrderItems as $item): ?>
                    <tr>
                        <td>
                            <?= sanitize($item['product_name'] ?? 'Ürün') ?>
                            <?php if (!empty($item['variant_name'])): ?>
                                <div class="input-note">Varyant: <?= sanitize($item['variant_name']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['requires_input_value'])): ?>
                                <div class="input-note">Müşteri Bilgisi: <?= sanitize($item['requires_input_value']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $item['qty'] ?></td>
                        <td>
                            <?php if (empty($item['deliveries'])): ?>
                                <span class="badge warning">Teslimat Bekliyor</span>
                            <?php else: ?>
                                <ul class="delivery-codes">
                                    <?php foreach ($item['deliveries'] as $delivery): ?>
                                        <li>
                                            <?php if ($delivery['type'] === 'code'): ?>
                                                <button type="button" class="code-toggle" data-code="<?= sanitize($delivery['raw']) ?>">Kod: <?= sanitize($delivery['value']) ?></button>
                                            <?php else: ?>
                                                <span><?= sanitize($delivery['value']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <p class="input-note">Teslim edilen kodlar geçmişte saklanır, dilediğiniz zaman erişebilirsiniz.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="input-note">Kodları görmek için üzerine tıklayın.</p>
        </section>
        <script>
            document.querySelectorAll('.code-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const code = atob(this.dataset.code);
                    this.textContent = 'Kod: ' + code;
                    this.classList.add('revealed');
                });
            });
        </script>
    <?php endif; ?>
</section>
