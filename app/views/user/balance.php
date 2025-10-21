<section class="section-shell" aria-labelledby="balance-title">
    <?php include __DIR__ . '/partials/tabs.php'; ?>
    <header class="section-title">
        <h1 id="balance-title">Bakiyem</h1>
        <span>Cüzdanınızı anında doldurun</span>
    </header>
    <div class="profile-grid">
        <article class="profile-card">
            <h2>Mevcut Bakiye</h2>
            <p class="summary-total">₺<?= number_format((float)($user['balance'] ?? 0), 2) ?></p>
            <p>Bakiyenizi sipariş ödemelerinde kullanabilirsiniz.</p>
        </article>
        <article class="profile-card">
            <h2>Cüzdan Yükle</h2>
            <form method="post" action="/panel/balance/topup">
                <?= csrf_field() ?>
                <label for="topup-amount">Yüklenecek Tutar (₺)</label>
                <input id="topup-amount" name="amount" type="number" min="10" step="5" value="100" required>
                <fieldset>
                    <legend>Ödeme Yöntemi</legend>
                    <label><input type="radio" name="payment_method" value="mock" checked> Mock Kart</label>
                    <label><input type="radio" name="payment_method" value="paytr"> PayTR</label>
                    <label><input type="radio" name="payment_method" value="iyzico"> Iyzico</label>
                </fieldset>
                <button type="submit">Bakiyeme Ekle</button>
            </form>
            <p class="input-note">Minimum yükleme tutarı ₺10'dur. Ödeme onaylandığında bakiyeniz anında güncellenir.</p>
        </article>
    </div>
</section>
