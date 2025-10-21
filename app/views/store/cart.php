<?php $hasItems = !empty($cart); ?>
<section class="section-shell" aria-labelledby="cart-title">
    <header class="section-title">
        <h1 id="cart-title">Sepetiniz</h1>
        <span><?= $hasItems ? 'Siparişinizi kontrol edin ve ödeme adımına geçin.' : 'Sepetiniz şu anda boş.' ?></span>
    </header>
    <?php if (!$hasItems): ?>
        <p>Favori ürünlerinizi ekleyerek başlayın.</p>
    <?php else: ?>
        <div class="cart-layout">
            <div>
                <form method="post" action="/cart/update">
                    <?= csrf_field() ?>
                    <table class="cart-table">
                    <thead>
                    <tr>
                        <th scope="col">Ürün</th>
                        <th scope="col">Adet</th>
                        <th scope="col">Birim</th>
                        <th scope="col">Ara Toplam</th>
                        <th scope="col">İşlem</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $item): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($item['product']['name']) ?></strong>
                                <?php if (!empty($item['product']['variant_name'])): ?>
                                    <div class="input-note">Varyant: <?= sanitize($item['product']['variant_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['input_value'])): ?>
                                    <div class="input-note">Müşteri Bilgisi: <?= sanitize($item['input_value']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label class="sr-only" for="qty-<?= sanitize($item['key']) ?>">Adet</label>
                                <input id="qty-<?= sanitize($item['key']) ?>" type="number" name="quantity[<?= sanitize($item['key']) ?>]" value="<?= (int) $item['quantity'] ?>" min="1">
                            </td>
                            <td>₺<?= number_format((float) ($item['product']['price'] ?? 0), 2) ?></td>
                            <td>₺<?= number_format((float) ($item['product']['price'] ?? 0) * $item['quantity'], 2) ?></td>
                            <td><a href="/cart/remove/<?= sanitize($item['key'] ?? ($item['product']['id'] . '-0')) ?>">Kaldır</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    </table>
                    <div class="cart-actions">
                        <button class="secondary" type="submit">Sepeti Güncelle</button>
                    </div>
                </form>
            </div>
            <aside class="cart-summary" aria-labelledby="order-summary-title">
                <h2 id="order-summary-title">Sipariş Özeti</h2>
                <form method="post" action="/cart/coupon" aria-label="Kupon kodu">
                    <?= csrf_field() ?>
                    <label for="coupon-code">Kupon</label>
                    <input id="coupon-code" type="text" name="coupon" value="<?= sanitize($coupon['code'] ?? '') ?>" placeholder="KODUNUZU GİRİN">
                    <button class="secondary" type="submit">Uygula</button>
                </form>
                <div class="summary-row">
                    <span>Ara Toplam</span>
                    <span>₺<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>İndirim</span>
                    <span>₺<?= number_format($discount, 2) ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Genel Toplam</span>
                    <span>₺<?= number_format($total, 2) ?></span>
                </div>
                <a class="button" href="/checkout">Ödemeye Geç</a>
                <div class="trust-badges" aria-label="Güven rozetleri">
                    <span>256-bit SSL</span>
                    <span>3D Secure</span>
                    <span>Anında Teslim</span>
                    <span>7/24 Destek</span>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</section>
