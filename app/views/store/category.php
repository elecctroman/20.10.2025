<?php
$deliveryLabels = [
    'auto' => 'Otomatik Teslim',
    'instant' => 'Anında Teslim',
    'manual' => 'Manuel Onay',
];

$filters = $filters ?? [];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1, 'total' => count($products), 'per_page' => 12];
$variantOptions = $variantOptions ?? [];
$priceRange = $priceRange ?? ['min' => 0, 'max' => 0];

$baseParams = [];
foreach (['delivery', 'variant', 'price_min', 'price_max', 'sort'] as $key) {
    if (!empty($filters[$key])) {
        $baseParams[$key] = $filters[$key];
    }
}

$buildQuery = function (array $overrides = []) use ($baseParams) {
    $query = array_merge($baseParams, $overrides);
    $query = array_filter($query, function ($value) {
        return $value !== null && $value !== '';
    });

    return http_build_query($query);
};
?>
<section class="section-shell" aria-labelledby="category-title">
    <header class="section-title">
        <h1 id="category-title"><?= sanitize($category['name']) ?></h1>
        <span><?= sanitize($category['description'] ?? '') ?></span>
    </header>
    <form class="category-filter" method="get" aria-label="Kategori filtreleri">
        <div class="filter-group">
            <label for="delivery">Teslimat</label>
            <select id="delivery" name="delivery">
                <option value="">Tümü</option>
                <?php foreach ($deliveryLabels as $value => $label): ?>
                    <option value="<?= sanitize($value) ?>" <?= ($filters['delivery'] ?? '') === $value ? 'selected' : '' ?>><?= sanitize($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="price_min">Fiyat (Min)</label>
            <input id="price_min" name="price_min" type="number" min="0" step="0.01" placeholder="<?= number_format($priceRange['min'] ?? 0, 0) ?>" value="<?= sanitize($filters['price_min'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <label for="price_max">Fiyat (Max)</label>
            <input id="price_max" name="price_max" type="number" min="0" step="0.01" placeholder="<?= number_format($priceRange['max'] ?? 0, 0) ?>" value="<?= sanitize($filters['price_max'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <label for="variant">Varyant</label>
            <select id="variant" name="variant">
                <option value="">Tümü</option>
                <?php foreach ($variantOptions as $option): ?>
                    <option value="<?= sanitize($option) ?>" <?= ($filters['variant'] ?? '') === $option ? 'selected' : '' ?>><?= sanitize($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="sort">Sırala</label>
            <select id="sort" name="sort">
                <option value="featured" <?= ($filters['sort'] ?? 'featured') === 'featured' ? 'selected' : '' ?>>Öne Çıkanlar</option>
                <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Yeni Eklenenler</option>
                <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Fiyat Artan</option>
                <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Fiyat Azalan</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit">Filtrele</button>
            <a class="reset" href="/kategori/<?= sanitize($category['slug']) ?>">Sıfırla</a>
        </div>
    </form>
    <p class="filter-summary">Toplam <?= (int) ($pagination['total'] ?? 0) ?> sonuç. Sayfa <?= (int) ($pagination['page'] ?? 1) ?> / <?= (int) ($pagination['pages'] ?? 1) ?>.</p>
    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p class="filter-summary">Seçili filtrelerle eşleşen ürün bulunamadı. Farklı kriterler deneyin.</p>
        <?php endif; ?>
        <div class="grid category-grid-responsive">
            <?php foreach ($products as $product): ?>
                <?php $prices = convert_price_multi($product['price'] ?? 0); ?>
                <article class="product-card">
                    <figure>
                        <img src="<?= sanitize($product['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
                        <span class="product-tag"><?= sanitize(mb_strtoupper(mb_substr($product['name'], 0, 14))) ?></span>
                        <span class="delivery-pill"><?= sanitize($deliveryLabels[$product['delivery_mode']] ?? 'Anında Teslim') ?></span>
                    </figure>
                    <h3><a href="/urun/<?= sanitize($product['slug']) ?>"><?= sanitize($product['name']) ?></a></h3>
                    <p><?= sanitize(mb_substr($product['short_desc'] ?? '', 0, 120)) ?><?= mb_strlen($product['short_desc'] ?? '') > 120 ? '…' : '' ?></p>
                    <div class="price-matrix" aria-label="Çoklu para birimi fiyatları">
                        <span class="price primary">₺<?= number_format((float) ($prices['TRY'] ?? 0), 2) ?></span>
                        <?php if (isset($prices['USD'])): ?>
                            <span class="price">$<?= number_format((float) $prices['USD'], 2) ?></span>
                        <?php endif; ?>
                        <?php if (isset($prices['EUR'])): ?>
                            <span class="price">€<?= number_format((float) $prices['EUR'], 2) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($product['variant_names'])): ?>
                        <ul class="variant-chips" aria-label="Mevcut varyantlar">
                            <?php foreach (array_slice($product['variant_names'], 0, 3) as $variantName): ?>
                                <li><?= sanitize($variantName) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <div class="product-actions">
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
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
        <nav class="pagination" aria-label="Sayfalama">
            <?php for ($page = 1; $page <= (int) $pagination['pages']; $page++): ?>
                <?php $isActive = $page === (int) $pagination['page']; ?>
                <a class="<?= $isActive ? 'active' : '' ?>" href="?<?= sanitize($buildQuery(['page' => $page])) ?>" aria-current="<?= $isActive ? 'page' : 'false' ?>"><?= $page ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
