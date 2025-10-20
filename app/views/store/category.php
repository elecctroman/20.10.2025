<?php
$deliveryFilters = array_unique(array_map(function ($item) {
    return $item['delivery_mode'] ?? 'auto';
}, $products));
$deliveryLabels = [
    'auto' => 'Otomatik Teslim',
    'instant' => 'Anında Teslim',
    'manual' => 'Manuel Onay'
];
?>
<section class="section-shell" aria-labelledby="category-title">
    <header class="section-title">
        <h1 id="category-title"><?= sanitize($category['name']) ?></h1>
        <span><?= sanitize($category['description'] ?? '') ?></span>
    </header>
    <div class="category-tags" role="list">
        <?php foreach ($deliveryFilters as $mode): ?>
            <span role="listitem" class="<?= $mode === ($activeFilter ?? null) ? 'active' : '' ?>"><?= sanitize($deliveryLabels[$mode] ?? ucfirst($mode)) ?></span>
        <?php endforeach; ?>
    </div>
    <div class="product-grid">
        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <figure>
                        <img src="<?= sanitize($product['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
                        <span class="product-tag"><?= sanitize(mb_strtoupper(mb_substr($product['name'], 0, 14))) ?></span>
                    </figure>
                    <h3><a href="/urun/<?= sanitize($product['slug']) ?>"><?= sanitize($product['name']) ?></a></h3>
                    <p><?= sanitize(mb_substr($product['short_desc'] ?? '', 0, 120)) ?><?= mb_strlen($product['short_desc'] ?? '') > 120 ? '…' : '' ?></p>
                    <div class="product-actions">
                        <span class="product-price">₺<?= number_format((float) ($product['price'] ?? 0), 2) ?></span>
                        <a class="button secondary" href="/urun/<?= sanitize($product['slug']) ?>">Sepete Ekle</a>
                    </div>
                    <div class="quantity-hover" data-quick-add="<?= sanitize($product['slug']) ?>">
                        <button type="button" aria-label="Adedi azalt" data-step="-1">−</button>
                        <output>1</output>
                        <button type="button" aria-label="Adedi artır" data-step="1">+</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
