<article class="product-detail" aria-labelledby="product-title">
    <h1 id="product-title"><?= sanitize($product['name']) ?></h1>
    <?php if (!empty($product['short_desc'])): ?>
        <p class="lead"><?= nl2br(sanitize($product['short_desc'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($product['long_desc'])): ?>
        <div class="product-description"><?= sanitize_html($product['long_desc']) ?></div>
    <?php endif; ?>
    <p class="stock" role="status">Stok: <?= (int) ($product['stock_visible'] ?? $stock) ?></p>
    <form action="/cart/add" method="post" class="buy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="slug" value="<?= sanitize($product['slug']) ?>">
        <?php if (!empty($product['requires_input'])): ?>
            <label for="user-input" class="input-label"><?= sanitize($product['input_label'] ?? 'Bilgi Giriniz') ?></label>
            <input id="user-input" name="inputs[<?= (int) $product['id'] ?>]" type="text" required>
        <?php endif; ?>
        <label for="quantity">Adet</label>
        <input id="quantity" name="quantity" type="number" min="<?= (int) ($product['min_qty'] ?? 1) ?>" value="<?= (int) ($product['min_qty'] ?? 1) ?>">
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
