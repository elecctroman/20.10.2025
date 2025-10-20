<section aria-labelledby="category-title">
    <h1 id="category-title"><?= sanitize($category['name']) ?></h1>
    <p><?= sanitize($category['description']) ?></p>
</section>
<section class="product-grid" aria-labelledby="category-products">
    <h2 id="category-products">Ürünler</h2>
    <div class="grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <h3><a href="/urun/<?= sanitize($product['slug']) ?>"><?= sanitize($product['name']) ?></a></h3>
                <p><?= sanitize(mb_substr($product['short_desc'] ?? '', 0, 120)) ?><?= mb_strlen($product['short_desc'] ?? '') > 120 ? '…' : '' ?></p>
                <p class="price">₺<?= number_format($product['price'], 2) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
