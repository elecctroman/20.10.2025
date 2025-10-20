<?php $total = array_reduce($cart, fn($sum, $item) => $sum + (($item['product']['price'] ?? 0) * $item['quantity']), 0.0); ?>
<section class="section-shell" aria-labelledby="checkout-title">
    <header class="section-title">
        <h1 id="checkout-title">Ödeme</h1>
        <span>Güvenli ve hızlı ödeme adımı</span>
    </header>
    <div class="checkout-grid">
        <form method="post" action="/checkout" class="checkout-card">
            <?= csrf_field() ?>
            <fieldset>
                <legend>İletişim Bilgileri</legend>
                <label for="email">E-posta</label>
                <input id="email" name="email" type="email" value="<?= sanitize($user['email'] ?? '') ?>" required>
                <label for="customer-note">Sipariş Notu</label>
                <textarea id="customer-note" name="customer_note" rows="3" maxlength="500" placeholder="Teslimat için ek bilgiler"></textarea>
            </fieldset>
            <fieldset class="payment-options">
                <legend>Ödeme Yöntemi</legend>
                <label><input type="radio" name="payment_method" value="mock" checked> Kredi/Banka Kartı (Mock)</label>
                <label><input type="radio" name="payment_method" value="paytr"> PayTR</label>
                <label><input type="radio" name="payment_method" value="iyzico"> Iyzico</label>
                <?php if (!empty($user)): ?>
                    <label><input type="radio" name="payment_method" value="wallet"> Cüzdan Bakiyesi (₺<?= number_format((float) ($user['balance'] ?? 0), 2) ?>)</label>
                <?php endif; ?>
            </fieldset>
            <label class="terms-check">
                <input type="checkbox" name="terms" value="1" required>
                <span><a href="/sayfa/mesafeli-satis">Mesafeli Satış Sözleşmesi</a> ve <a href="/sayfa/gizlilik">Gizlilik Politikası</a>’nı okudum, kabul ediyorum.</span>
            </label>
            <button type="submit">₺<?= number_format($total, 2) ?> tutarında ödemeyi tamamla</button>
        </form>
        <aside class="checkout-card" aria-labelledby="summary-title">
            <h2 id="summary-title">Sipariş Özeti</h2>
            <ul>
                <?php foreach ($cart as $item): ?>
                    <li>
                        <strong><?= sanitize($item['product']['name']) ?></strong> x <?= (int) $item['quantity'] ?>
                        <?php if (!empty($item['product']['variant_name'])): ?>
                            <div class="input-note">Varyant: <?= sanitize($item['product']['variant_name']) ?></div>
                        <?php endif; ?>
                        <div class="summary-row"><span></span><span>₺<?= number_format((float) ($item['product']['price'] ?? 0) * $item['quantity'], 2) ?></span></div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="summary-row"><span>Ara Toplam</span><span>₺<?= number_format($total, 2) ?></span></div>
            <div class="summary-row"><span>İndirim</span><span>₺0,00</span></div>
            <div class="summary-row summary-total"><span>Ödenecek</span><span>₺<?= number_format($total, 2) ?></span></div>
            <div class="trust-badges">
                <span>3D Secure</span>
                <span>PCI DSS</span>
                <span>Fraud Shield</span>
                <span>7/24 Destek</span>
            </div>
        </aside>
    </div>
</section>
