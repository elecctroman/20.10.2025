<section class="section-shell" aria-labelledby="payment-result-title">
    <header class="section-title">
        <h1 id="payment-result-title">Ödeme Sonucu</h1>
        <span>İşlem özeti</span>
    </header>
    <div class="profile-card">
        <p><?= sanitize($message ?? 'Ödeme durumu bilinmiyor.') ?></p>
        <p><a class="button" href="/">Anasayfaya dön</a></p>
    </div>
</section>
