<section class="section-shell" aria-labelledby="search-title">
    <header class="section-title">
        <h1 id="search-title">Arama Sonuçları</h1>
        <span><?= $query ? '“' . sanitize($query) . '” için sonuçlar' : 'Bir ürün arayın' ?></span>
    </header>
    <?php if (empty($results)): ?>
        <p><?= $query ? 'Aradığınız kriterlere uygun ürün bulunamadı.' : 'Ürün veya marka adı girerek arama yapabilirsiniz.' ?></p>
        <p class="input-note">Başlık, slug veya etiketlerle arama yapabilirsiniz.</p>
    <?php else: ?>
        <div class="product-grid">
            <div class="grid">
                <?php foreach ($results as $item): ?>
                    <?php $prices = convert_price_multi($item['price'] ?? 0); ?>
                    <article class="product-card">
                        <figure>
                            <img src="<?= sanitize($item['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy">
                            <span class="product-tag"><?= sanitize(mb_strtoupper(mb_substr($item['name'], 0, 12))) ?></span>
                        </figure>
                        <h3><a href="/urun/<?= sanitize($item['slug']) ?>"><?= sanitize($item['name']) ?></a></h3>
                        <p><?= sanitize(mb_substr($item['short_desc'] ?? '', 0, 120)) ?><?= mb_strlen($item['short_desc'] ?? '') > 120 ? '…' : '' ?></p>
                        <div class="price-matrix">
                            <span class="price primary">₺<?= number_format((float) ($prices['TRY'] ?? 0), 2) ?></span>
                            <?php if (isset($prices['USD'])): ?>
                                <span class="price">$<?= number_format((float) $prices['USD'], 2) ?></span>
                            <?php endif; ?>
                            <?php if (isset($prices['EUR'])): ?>
                                <span class="price">€<?= number_format((float) $prices['EUR'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-actions">
                            <a class="button secondary" href="/urun/<?= sanitize($item['slug']) ?>">İncele</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
