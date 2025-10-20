<article class="product-detail" aria-labelledby="product-title">
    <h1 id="product-title"><?= sanitize($product['name']) ?></h1>
    <p><?= nl2br(sanitize($product['description'])) ?></p>
    <p class="stock" role="status">Stok: <?= (int) $stock ?></p>
    <form action="/cart/add" method="post" class="buy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="slug" value="<?= sanitize($product['slug']) ?>">
        <label for="quantity">Adet</label>
        <input id="quantity" name="quantity" type="number" min="1" value="1">
        <button type="submit">Sepete Ekle - ₺<?= number_format($product['price'], 2) ?></button>
    </form>
</article>
<?php if ($variants): ?>
<section aria-labelledby="variants-heading">
    <h2 id="variants-heading">Varyantlar</h2>
    <ul class="variant-list">
        <?php foreach ($variants as $variant): ?>
            <li><?= sanitize($variant['name']) ?> - ₺<?= number_format($variant['price'], 2) ?></li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>
