<section aria-labelledby="register-title" class="auth-form">
    <h1 id="register-title">Kayıt Ol</h1>
    <form method="post" action="/register">
        <?= csrf_field() ?>
        <label for="name">Ad</label>
        <input id="name" name="name" type="text" required>
        <label for="surname">Soyad</label>
        <input id="surname" name="surname" type="text" required>
        <label for="phone">Telefon</label>
        <input id="phone" name="phone" type="tel" pattern="[0-9+\s-]*" autocomplete="tel">
        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" required>
        <label for="password">Şifre</label>
        <input id="password" name="password" type="password" required>
        <button type="submit">Kayıt Ol</button>
    </form>
    <p><a href="/login">Giriş yap</a></p>
</section>
