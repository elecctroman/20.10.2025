<?php
$featured = array_slice($products, 0, 3);
$currencyProducts = array_values(array_filter($products, function (array $product) {
    $name = mb_strtolower($product['name'] ?? '');
    return str_contains($name, 'uc') || str_contains($name, 'vp') || str_contains($name, 'coin');
}));
$subscriptionProducts = array_values(array_filter($products, function (array $product) {
    $name = mb_strtolower($product['name'] ?? '');
    return str_contains($name, 'lisans') || str_contains($name, 'pro') || str_contains($name, 'premium');
}));
if (!$currencyProducts) {
    $currencyProducts = array_slice($products, 0, 6);
}
if (!$subscriptionProducts) {
    $subscriptionProducts = array_slice($products, 3, 6);
}
?>
<section class="hero-shell" aria-labelledby="hero-heading">
    <div class="hero-slider" data-slider>
        <div class="hero-slides">
            <?php foreach ($featured as $index => $item): ?>
                <article class="hero-slide" data-index="<?= $index ?>">
                    <div>
                        <h2 id="hero-heading"><?= sanitize($item['name']) ?></h2>
                        <p><?= sanitize(mb_substr($item['short_desc'] ?? '', 0, 160)) ?><?= mb_strlen($item['short_desc'] ?? '') > 160 ? '…' : '' ?></p>
                        <?php $discount = 5 + (((int) ($item['id'] ?? 1)) % 15); ?>
                        <ul class="campaign-badges" role="list">
                            <li role="listitem">%<?= $discount ?> indirim</li>
                            <li role="listitem">Stok: <?= (int) ($item['stock_visible'] ?? 0) ?></li>
                            <li role="listitem">Gece teslimatı</li>
                        </ul>
                        <div class="product-price">₺<?= number_format((float) ($item['price'] ?? 0), 2) ?></div>
                        <div class="delivery-badges" aria-label="Teslimat seçenekleri">
                            <span>⚡ Otomatik Teslim</span>
                            <span>🛡️ Güvenli Ödeme</span>
                            <span>💬 7/24 Destek</span>
                        </div>
                        <p><a class="button" href="/urun/<?= sanitize($item['slug']) ?>">Hemen İncele</a></p>
                    </div>
                    <figure>
                        <img src="<?= sanitize($item['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy">
                        <figcaption class="product-tag"><?= sanitize($item['name']) ?></figcaption>
                    </figure>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="hero-controls" role="tablist" aria-label="Öne çıkan kampanyalar">
            <?php foreach ($featured as $index => $_): ?>
                <button class="hero-control" type="button" data-slide="<?= $index ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slayt <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php if (!empty($categories)): ?>
<section class="section-shell category-showcase" aria-labelledby="category-showcase-heading">
    <div class="section-title">
        <h2 id="category-showcase-heading">Trend Kategoriler</h2>
        <span>En çok ziyaret edilen oyun ve lisans koleksiyonları</span>
    </div>
    <div class="category-grid">
        <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
            <article class="category-card">
                <h3><a href="/kategori/<?= sanitize($cat['slug']) ?>"><?= sanitize($cat['name']) ?></a></h3>
                <?php if (!empty($cat['description'])): ?>
                    <p><?= sanitize(mb_substr($cat['description'], 0, 80)) ?><?= mb_strlen($cat['description']) > 80 ? '…' : '' ?></p>
                <?php else: ?>
                    <p>Dijital ürünlerde özel avantajlar sizi bekliyor.</p>
                <?php endif; ?>
                <a class="category-link" href="/kategori/<?= sanitize($cat['slug']) ?>" aria-label="<?= sanitize($cat['name']) ?> kategorisini görüntüle">Keşfet</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<section class="section-shell" aria-labelledby="currency-heading">
    <div class="section-title">
        <h2 id="currency-heading">Oyun Para Birimleri</h2>
        <span>En popüler UC ve VP paketleri</span>
    </div>
    <div class="product-grid">
        <div class="grid">
            <?php foreach ($currencyProducts as $item): ?>
                <article class="product-card">
                    <figure>
                        <img src="<?= sanitize($item['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy">
                        <span class="product-tag"><?= sanitize(mb_strtoupper(mb_substr($item['name'], 0, 12))) ?></span>
                    </figure>
                    <h3><a href="/urun/<?= sanitize($item['slug']) ?>"><?= sanitize($item['name']) ?></a></h3>
                    <p><?= sanitize(mb_substr($item['short_desc'] ?? '', 0, 110)) ?><?= mb_strlen($item['short_desc'] ?? '') > 110 ? '…' : '' ?></p>
                    <div class="product-actions">
                        <span class="product-price">₺<?= number_format((float) ($item['price'] ?? 0), 2) ?></span>
                        <a class="button secondary" href="/urun/<?= sanitize($item['slug']) ?>">Sepete Ekle</a>
                    </div>
                    <div class="quantity-hover" data-quick-add="<?= sanitize($item['slug']) ?>">
                        <button type="button" aria-label="Adedi azalt" data-step="-1">−</button>
                        <output>1</output>
                        <button type="button" aria-label="Adedi artır" data-step="1">+</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="section-shell" aria-labelledby="subscription-heading">
    <div class="section-title">
        <h2 id="subscription-heading">Abonelikler &amp; Lisanslar</h2>
        <span>İşinizi hızlandıran profesyonel araçlar</span>
    </div>
    <div class="product-grid">
        <div class="grid">
            <?php foreach ($subscriptionProducts as $item): ?>
                <article class="product-card">
                    <figure>
                        <img src="<?= sanitize($item['image'] ?? asset('img/placeholders/placeholder.png')) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy">
                        <span class="product-tag"><?= sanitize(mb_strtoupper(mb_substr($item['name'], 0, 14))) ?></span>
                    </figure>
                    <h3><a href="/urun/<?= sanitize($item['slug']) ?>"><?= sanitize($item['name']) ?></a></h3>
                    <p><?= sanitize(mb_substr($item['short_desc'] ?? '', 0, 110)) ?><?= mb_strlen($item['short_desc'] ?? '') > 110 ? '…' : '' ?></p>
                    <div class="product-actions">
                        <span class="product-price">₺<?= number_format((float) ($item['price'] ?? 0), 2) ?></span>
                        <a class="button secondary" href="/urun/<?= sanitize($item['slug']) ?>">Sepete Ekle</a>
                    </div>
                    <div class="quantity-hover" data-quick-add="<?= sanitize($item['slug']) ?>">
                        <button type="button" aria-label="Adedi azalt" data-step="-1">−</button>
                        <output>1</output>
                        <button type="button" aria-label="Adedi artır" data-step="1">+</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="section-shell" aria-labelledby="blog-heading">
    <div class="section-title">
        <h2 id="blog-heading">Blogdan Öne Çıkanlar</h2>
        <span>Dijital dünyadan güncel haberler</span>
    </div>
    <div class="blog-grid">
        <?php foreach (array_slice($posts, 0, 3) as $post): ?>
            <article class="blog-card">
                <h3><a href="/blog/<?= sanitize($post['slug']) ?>"><?= sanitize($post['title']) ?></a></h3>
                <p><?= sanitize(mb_substr($post['excerpt'] ?? '', 0, 120)) ?><?= mb_strlen($post['excerpt'] ?? '') > 120 ? '…' : '' ?></p>
                <?php if (!empty($post['published_at'])): ?>
                    <time datetime="<?= sanitize($post['published_at']) ?>"><?= date('d.m.Y', strtotime($post['published_at'])) ?></time>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
