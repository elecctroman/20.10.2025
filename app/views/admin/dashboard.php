<section aria-labelledby="admin-dashboard-title">
    <h1 id="admin-dashboard-title">Yönetici Özeti</h1>
    <p>Hoş geldiniz, <?= sanitize($user['name'] ?? 'Admin') ?></p>
    <div class="stats">
        <div>
            <h2>Ürün Sayısı</h2>
            <p><?= (int) $productCount ?></p>
        </div>
        <div>
            <h2>Son Siparişler</h2>
            <ul>
                <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                    <li>#<?= (int) $order['id'] ?> - ₺<?= number_format($order['total_amount'], 2) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
