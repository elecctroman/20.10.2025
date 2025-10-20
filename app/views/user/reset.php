<section aria-labelledby="reset-title" class="auth-form">
    <h1 id="reset-title">Şifre Sıfırlama</h1>
    <form method="post" action="/password/email">
        <?= csrf_field() ?>
        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" required>
        <button type="submit">Bağlantı Gönder</button>
    </form>
</section>
