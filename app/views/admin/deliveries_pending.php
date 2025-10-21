<section class="admin-section" aria-labelledby="pending-title">
    <header class="admin-header">
        <h1 id="pending-title">Eksik Teslimatlar</h1>
        <p>Ödemesi tamamlanmış fakat teslim bekleyen siparişleri yönetin.</p>
    </header>
    <?php if (empty($orders)): ?>
        <p>Teslim edilmesi bekleyen sipariş bulunmuyor.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <article class="delivery-card">
                <header>
                    <h2>#<?= (int) $order['id'] ?> — <?= sanitize($order['email'] ?? 'Misafir') ?></h2>
                    <span><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                </header>
                <table>
                    <thead>
                    <tr>
                        <th scope="col">Ürün</th>
                        <th scope="col">Adet</th>
                        <th scope="col">Durum</th>
                        <th scope="col">İşlem</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <?= sanitize($item['product_name']) ?>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <div class="input-note">Varyant: <?= sanitize($item['variant_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= (int) $item['qty'] ?>
                                <?php if (!empty($item['delivered_count'])): ?>
                                    <div class="input-note">Teslim edilen: <?= (int) $item['delivered_count'] ?></div>
                                <?php endif; ?>
                            </td>
                            <td>Kalan: <?= (int) $item['remaining'] ?></td>
                            <td>
                                <form method="post" action="/admin/deliveries/assign/<?= (int) $item['id'] ?>" class="delivery-form">
                                    <?= csrf_field() ?>
                                    <textarea name="codes" rows="3" placeholder="Her satıra bir kod" required></textarea>
                                    <input type="text" name="note" placeholder="Opsiyonel teslim notu">
                                    <button type="submit">Şimdi Teslim</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
