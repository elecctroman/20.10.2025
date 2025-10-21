<section aria-labelledby="login-title" class="auth-form">
    <h1 id="login-title">Giriş Yap</h1>
    <form method="post" action="/login">
        <?= csrf_field() ?>
        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" required autocomplete="email">
        <label for="password">Şifre</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
        <button type="submit">Giriş</button>
    </form>
    <p><a href="/register">Hesap oluştur</a></p>
</section>
