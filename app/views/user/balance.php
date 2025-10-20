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
            <form method="post" action="#">
                <label for="topup-amount">Yüklenecek Tutar</label>
                <input id="topup-amount" type="number" min="25" step="5" value="100">
                <button type="button">Yükleme Talebi Oluştur</button>
            </form>
        </article>
    </div>
</section>
