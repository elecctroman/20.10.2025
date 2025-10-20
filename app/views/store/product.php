<?php
$minQty = (int) ($product['min_qty'] ?? 1);
$maxQty = (int) ($product['max_qty'] ?? 0);
$maxQty = $maxQty > 0 ? $maxQty : null;
$requiresInput = !empty($product['requires_input']);
$deliveryLabels = [
    'auto' => 'Otomatik Teslim',
    'instant' => 'Anında Teslim',
    'manual' => 'Manuel Onay'
];
$delivery = $deliveryLabels[$product['delivery_mode'] ?? 'auto'] ?? 'Otomatik Teslim';
?>
<article class="section-shell product-detail" aria-labelledby="product-title">
    <div class="product-gallery">
        <img src="<?= sanitize($product['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
    </div>
    <div>
        <header>
            <p class="product-price">₺<?= number_format((float) ($product['price'] ?? 0), 2) ?></p>
            <h1 id="product-title"><?= sanitize($product['name']) ?></h1>
            <div class="delivery-badges">
                <span>⚡ <?= sanitize($delivery) ?></span>
                <span>🛡️ Güvenli Ödeme</span>
                <span>📦 Stok: <?= (int) $stock ?></span>
            </div>
        </header>
        <?php if (!empty($product['short_desc'])): ?>
            <p class="lead"><?= nl2br(sanitize($product['short_desc'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($product['long_desc'])): ?>
            <div class="product-description"><?= sanitize_html($product['long_desc']) ?></div>
        <?php endif; ?>
        <form action="/cart/add" method="post" class="product-form" data-product-form>
            <?= csrf_field() ?>
            <input type="hidden" name="slug" value="<?= sanitize($product['slug']) ?>">
            <?php if (!empty($variants)): ?>
                <label for="variant-select">Varyant Seçimi</label>
                <select id="variant-select" name="variant_id">
                    <?php foreach ($variants as $variant): ?>
                        <option value="<?= (int) $variant['id'] ?>" data-price="<?= number_format((float) $variant['price'], 2) ?>">
                            <?= sanitize($variant['name']) ?> - ₺<?= number_format((float) $variant['price'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if ($requiresInput): ?>
                <label for="user-input"><?= sanitize($product['input_label'] ?? 'Kullanıcı Bilgisi') ?></label>
                <input id="user-input" name="inputs[<?= (int) $product['id'] ?>]" type="text" required>
            <?php endif; ?>
            <div>
                <label for="quantity">Adet</label>
                <div class="quantity-field" data-quantity>
                    <button type="button" data-step="-1" aria-label="Adedi azalt">−</button>
                    <input id="quantity" name="quantity" type="number" min="<?= $minQty ?>" <?= $maxQty ? 'max="' . $maxQty . '"' : '' ?> value="<?= $minQty ?>">
                    <button type="button" data-step="1" aria-label="Adedi artır">+</button>
                </div>
            </div>
            <button type="submit">Sepete Ekle</button>
        </form>
    </div>
</article>
<?php if (!empty($related)): ?>
<section class="section-shell related-products" aria-labelledby="related-title">
    <header class="section-title">
        <h2 id="related-title">Benzer Ürünler</h2>
        <?php if (!empty($category['name'])): ?>
            <span><?= sanitize($category['name']) ?> kategorisinden öneriler</span>
        <?php endif; ?>
    </header>
    <ul>
        <?php foreach ($related as $item): ?>
            <li>
                <article class="product-card">
                    <figure>
                        <img src="<?= sanitize($item['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy">
                        <span class="product-tag"><?= sanitize(mb_strtoupper(mb_substr($item['name'], 0, 12))) ?></span>
                    </figure>
                    <h3><a href="/urun/<?= sanitize($item['slug']) ?>"><?= sanitize($item['name']) ?></a></h3>
                    <div class="product-actions">
                        <span class="product-price">₺<?= number_format((float) ($item['price'] ?? 0), 2) ?></span>
                        <a class="button secondary" href="/urun/<?= sanitize($item['slug']) ?>">İncele</a>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>
