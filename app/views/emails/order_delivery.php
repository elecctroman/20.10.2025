<section>
    <h1 style="font-size:20px;">Siparişiniz teslim edildi (#<?= (int) $orderId ?>)</h1>
    <p>Satın aldığınız dijital ürünler hazır. Detaylar aşağıdadır:</p>
    <?php foreach ($payloads as $payload): ?>
        <article style="margin-bottom:16px;">
            <h2 style="font-size:16px;margin-bottom:8px;">
                <?= sanitize($payload['product']['name']) ?><?php if (!empty($payload['variant_name'])): ?> — <?= sanitize($payload['variant_name']) ?><?php endif; ?>
            </h2>
            <ul style="padding-left:16px;">
                <?php foreach ($payload['items'] as $item): ?>
                    <li style="margin-bottom:6px;">
                        <?php if (!empty($item['code'])): ?>
                            <?= sanitize(base64_decode($item['code'], true) ?: '') ?>
                        <?php elseif (!empty($item['note'])): ?>
                            <?= sanitize(base64_decode($item['note'], true) ?: '') ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endforeach; ?>
    <p>Toplam tutar: ₺<?= number_format((float) $total, 2) ?></p>
</section>
