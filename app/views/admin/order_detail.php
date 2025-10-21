<?php
$formatCurrency = static fn (float $value): string => '₺' . number_format($value, 2);
?>
<section class="admin-section" aria-labelledby="order-detail-title">
    <header class="admin-header">
        <h1 id="order-detail-title">Sipariş #<?= (int) $order['id'] ?></h1>
        <p><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?> · <?= sanitize(strtoupper($order['status'])) ?></p>
    </header>

    <div class="order-summary-grid">
        <article>
            <h2>Müşteri</h2>
            <p><?= $order['email'] ? sanitize($order['email']) : 'Misafir' ?></p>
            <?php if (!empty($order['name']) || !empty($order['surname'])): ?>
                <small><?= sanitize(trim(($order['name'] ?? '') . ' ' . ($order['surname'] ?? ''))) ?></small>
            <?php endif; ?>
            <?php if (!empty($order['ip'])): ?>
                <small>IP: <?= sanitize($order['ip']) ?></small>
            <?php endif; ?>
        </article>
        <article>
            <h2>Tutar</h2>
            <p><?= $formatCurrency((float) $order['total']) ?> <?= sanitize($order['currency'] ?? 'TRY') ?></p>
            <small>Oluşturuldu: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></small><br>
            <small>Güncellendi: <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></small>
        </article>
        <article>
            <h2>Notlar</h2>
            <?php if (!empty($order['customer_note'])): ?>
                <p><strong>Müşteri:</strong> <?= sanitize($order['customer_note']) ?></p>
            <?php endif; ?>
            <?php if (!empty($order['admin_note'])): ?>
                <p><strong>Admin:</strong> <?= sanitize($order['admin_note']) ?></p>
            <?php else: ?>
                <p>Henüz yönetici notu eklenmemiş.</p>
            <?php endif; ?>
        </article>
    </div>

    <section class="panel-section" aria-labelledby="order-items">
        <h2 id="order-items">Sipariş Öğeleri</h2>
        <table class="table-list">
            <thead>
            <tr>
                <th scope="col">Ürün</th>
                <th scope="col">Varyant</th>
                <th scope="col">Adet</th>
                <th scope="col">Birim</th>
                <th scope="col">Teslimat</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <?php
                $entries = json_decode($item['delivery_json'] ?? '', true) ?? [];
                ?>
                <tr>
                    <td><?= sanitize($item['product_name']) ?></td>
                    <td><?= sanitize($item['variant_name'] ?? 'Standart') ?></td>
                    <td><?= (int) $item['qty'] ?></td>
                    <td><?= $formatCurrency((float) $item['unit_price']) ?></td>
                    <td>
                        <?php if (empty($entries)): ?>
                            <span class="badge warning">Teslimat Bekliyor</span>
                        <?php else: ?>
                            <ul class="delivery-code-list">
                                <?php foreach ($entries as $entry): ?>
                                    <?php
                                    $code = !empty($entry['code']) ? base64_decode($entry['code'], true) : null;
                                    $note = !empty($entry['note']) ? base64_decode($entry['note'], true) : null;
                                    ?>
                                    <li>
                                        <?php if ($code): $masked = mask_string($code); ?>
                                            <span class="code-pill" data-mask="<?= sanitize($masked) ?>" data-code="<?= sanitize($code) ?>"><?= sanitize($masked) ?></span>
                                            <button type="button" class="button-ghost" data-action="toggle-code" aria-label="Kodu göster/gizle">Göster</button>
                                            <button type="button" class="button-ghost" data-action="copy-code" aria-label="Kodu kopyala">Kopyala</button>
                                        <?php elseif ($note): ?>
                                            <span><?= sanitize($note) ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="panel-section" aria-labelledby="payment-history">
        <h2 id="payment-history">Ödeme Geçmişi</h2>
        <?php if (empty($order['payments'])): ?>
            <p>Kayıtlı ödeme bulunamadı.</p>
        <?php else: ?>
            <table class="table-list">
                <thead>
                <tr>
                    <th scope="col">Gateway</th>
                    <th scope="col">Durum</th>
                    <th scope="col">Tutar</th>
                    <th scope="col">Txn ID</th>
                    <th scope="col">Tarih</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($order['payments'] as $payment): ?>
                    <tr>
                        <td><?= sanitize(strtoupper($payment['gateway'])) ?></td>
                        <td><span class="badge"><?= sanitize(strtoupper($payment['status'])) ?></span></td>
                        <td><?= $formatCurrency((float) $payment['amount']) ?></td>
                        <td><?= sanitize($payment['txn_id'] ?? '-') ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($payment['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="panel-section" aria-labelledby="order-actions">
        <h2 id="order-actions">İşlemler</h2>
        <div class="form-grid">
            <form method="post" action="/admin/orders/<?= (int) $order['id'] ?>/status">
                <?= csrf_field() ?>
                <label for="status">Sipariş Durumu</label>
                <select id="status" name="status" required>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= sanitize($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= strtoupper($status) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="admin_note">Yönetici Notu</label>
                <textarea id="admin_note" name="admin_note" rows="3" placeholder="İç notlar için kullanılır."><?= sanitize($order['admin_note'] ?? '') ?></textarea>
                <button type="submit">Durumu Güncelle</button>
            </form>
            <form method="post" action="/admin/orders/<?= (int) $order['id'] ?>/resend">
                <?= csrf_field() ?>
                <p>E-posta yeniden gönderimi müşteri e-posta adresini kullanır.</p>
                <button type="submit" <?= empty($payloads) ? 'disabled' : '' ?>>Teslimat E-postasını Yeniden Gönder</button>
            </form>
        </div>
    </section>
</section>
