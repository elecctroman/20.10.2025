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
$prices = convert_price_multi($product['price'] ?? 0);
$currencyIcons = [
    'TRY' => '₺',
    'USD' => '$',
    'EUR' => '€',
];
?>
<article class="section-shell product-detail" aria-labelledby="product-title">
    <div class="product-gallery">
        <img src="<?= sanitize($product['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
    </div>
    <div>
        <header>
            <div class="product-price-stack" data-prices='<?= htmlspecialchars(json_encode($prices, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>' aria-label="Çoklu para birimi fiyatları">
                <?php foreach ($prices as $code => $value): ?>
                    <span class="price-chip <?= $code === 'TRY' ? 'primary' : '' ?>"><?= $currencyIcons[$code] ?? '' ?><?= number_format((float) $value, 2) ?> <?= $code !== 'TRY' ? $code : '' ?></span>
                <?php endforeach; ?>
            </div>
            <h1 id="product-title"><?= sanitize($product['name']) ?></h1>
            <div class="delivery-badges">
                <span>⚡ <?= sanitize($delivery) ?></span>
                <span>🚀 Şimdi Teslim</span>
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
                        <?php $variantConversions = convert_price_multi($variant['price']); ?>
                        <option value="<?= (int) $variant['id'] ?>"
                                data-prices='<?= htmlspecialchars(json_encode($variantConversions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
                            <?= sanitize($variant['name']) ?> - ₺<?= number_format((float) $variant['price'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if ($requiresInput): ?>
                <label for="user-input"><?= sanitize($product['input_label'] ?? 'Kullanıcı Bilgisi') ?></label>
                <input id="user-input" name="inputs[<?= (int) $product['id'] ?>]" type="text" required placeholder="<?= sanitize($product['input_label'] ?? 'örn. ID / Kullanıcı Adı') ?>">
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
    <ul class="related-grid">
        <?php foreach ($related as $item): ?>
            <?php $relatedPrices = convert_price_multi($item['price'] ?? 0); ?>
            <li>
                <article class="product-card">
                    <figure>
                        <img src="<?= sanitize($item['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy">
                        <span class="product-tag"><?= sanitize(mb_strtoupper(mb_substr($item['name'], 0, 12))) ?></span>
                    </figure>
                    <h3><a href="/urun/<?= sanitize($item['slug']) ?>"><?= sanitize($item['name']) ?></a></h3>
                    <div class="price-matrix">
                        <span class="price primary">₺<?= number_format((float) ($relatedPrices['TRY'] ?? 0), 2) ?></span>
                        <?php if (isset($relatedPrices['USD'])): ?>
                            <span class="price">$<?= number_format((float) $relatedPrices['USD'], 2) ?></span>
                        <?php endif; ?>
                        <?php if (isset($relatedPrices['EUR'])): ?>
                            <span class="price">€<?= number_format((float) $relatedPrices['EUR'], 2) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a class="button secondary" href="/urun/<?= sanitize($item['slug']) ?>">İncele</a>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>
