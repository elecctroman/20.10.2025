<?php
$formatCurrency = static fn (float $value): string => '₺' . number_format($value, 2);
?>
<section class="admin-section" aria-labelledby="admin-orders-title">
    <header class="admin-header">
        <h1 id="admin-orders-title">Sipariş Yönetimi</h1>
        <p>Siparişleri filtreleyin, durumları yönetin ve teslimatları takip edin.</p>
    </header>

    <form method="get" class="filter-bar" aria-label="Sipariş filtreleri">
        <label>
            Durum
            <select name="status">
                <option value="">Tümü</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= sanitize($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= strtoupper($status) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Ödeme Sağlayıcı
            <select name="gateway">
                <option value="">Tümü</option>
                <?php foreach ($gateways as $gateway): ?>
                    <option value="<?= sanitize($gateway) ?>" <?= ($filters['gateway'] ?? '') === $gateway ? 'selected' : '' ?>><?= strtoupper($gateway) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Başlangıç
            <input type="date" name="from" value="<?= sanitize($filters['from'] ?? '') ?>">
        </label>
        <label>
            Bitiş
            <input type="date" name="to" value="<?= sanitize($filters['to'] ?? '') ?>">
        </label>
        <label>
            Ara (ID/E-posta)
            <input type="search" name="q" value="<?= sanitize($filters['q'] ?? '') ?>" placeholder="#123 veya mail">
        </label>
        <button type="submit">Filtrele</button>
        <a class="button-ghost" href="/admin/orders">Sıfırla</a>
    </form>

    <div class="table-scroll">
        <table class="table-list">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Müşteri</th>
                <th scope="col">Tutar</th>
                <th scope="col">Durum</th>
                <th scope="col">Ödeme</th>
                <th scope="col">Tarih</th>
                <th scope="col">İşlem</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7">Kriterlere uygun sipariş bulunamadı.</td></tr>
            <?php else: ?>
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
                        <td><span class="badge <?= sanitize($order['status']) ?>"><?= strtoupper($order['status']) ?></span></td>
                        <td>
                            <?php if (!empty($order['last_gateway'])): ?>
                                <?= sanitize(strtoupper($order['last_gateway'])) ?>
                                <?php if (!empty($order['payment_status'])): ?>
                                    <small class="badge subtle"><?= sanitize(strtoupper($order['payment_status'])) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                        <td><a class="button-ghost" href="/admin/orders/<?= (int) $order['id'] ?>">Detay</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($pagination) && $pagination['pages'] > 1): ?>
        <nav class="pagination" aria-label="Sipariş sayfaları">
            <?php for ($page = 1; $page <= $pagination['pages']; $page++): $isCurrent = $pagination['page'] === $page; ?>
                <a class="<?= $isCurrent ? 'active' : '' ?>" href="<?= route('admin/orders') ?>?<?= http_build_query(array_merge($filters, ['page' => $page])) ?>" aria-current="<?= $isCurrent ? 'page' : 'false' ?>"><?= $page ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
