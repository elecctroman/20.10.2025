<section class="hero" aria-labelledby="hero-heading">
    <h1 id="hero-heading">Dijital Ürünlerde Güvenli Teslimat</h1>
    <p>Binlerce E-PİN ve oyun koduna anında erişin.</p>
</section>
<section class="product-grid" aria-labelledby="popular-products">
    <h2 id="popular-products">Popüler Ürünler</h2>
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
<section class="blog-list" aria-labelledby="latest-posts">
    <h2 id="latest-posts">Güncel Yazılar</h2>
    <ul>
        <?php foreach ($posts as $post): ?>
            <li>
                <a href="/blog/<?= sanitize($post['slug']) ?>"><?= sanitize($post['title']) ?></a>
                <?php if (!empty($post['published_at'])): ?>
                    <time datetime="<?= sanitize($post['published_at']) ?>"><?= date('d.m.Y', strtotime($post['published_at'])) ?></time>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
