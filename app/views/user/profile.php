<?php
$profileErrors = $_SESSION['_form_errors']['profile'] ?? [];
$passwordErrors = $_SESSION['_form_errors']['password'] ?? [];
unset($_SESSION['_form_errors']['profile'], $_SESSION['_form_errors']['password']);
?>
<section class="section-shell" aria-labelledby="profile-title">
    <?php include __DIR__ . '/partials/tabs.php'; ?>
    <header class="section-title">
        <h1 id="profile-title">Profilim</h1>
        <span>Hesap ve iletişim bilgileriniz</span>
    </header>
    <div class="profile-grid">
        <article class="profile-card">
            <h2>Kişisel Bilgiler</h2>
            <form action="/panel/profile" method="post" class="stacked-form" novalidate>
                <?= csrf_field() ?>
                <div class="form-control<?= !empty($profileErrors['name']) ? ' has-error' : '' ?>">
                    <label for="name">Ad</label>
                    <input id="name" name="name" type="text" value="<?= sanitize($user['name'] ?? '') ?>" required>
                    <?php if (!empty($profileErrors['name'])): ?>
                        <span class="error-text"><?= sanitize($profileErrors['name'][0]) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-control<?= !empty($profileErrors['surname']) ? ' has-error' : '' ?>">
                    <label for="surname">Soyad</label>
                    <input id="surname" name="surname" type="text" value="<?= sanitize($user['surname'] ?? '') ?>" required>
                    <?php if (!empty($profileErrors['surname'])): ?>
                        <span class="error-text"><?= sanitize($profileErrors['surname'][0]) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-control<?= !empty($profileErrors['email']) ? ' has-error' : '' ?>">
                    <label for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="<?= sanitize($user['email'] ?? '') ?>" required>
                    <?php if (!empty($profileErrors['email'])): ?>
                        <span class="error-text"><?= sanitize($profileErrors['email'][0]) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-control">
                    <label for="phone">Telefon</label>
                    <input id="phone" name="phone" type="tel" value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="05xx xxx xx xx">
                </div>
                <button type="submit">Bilgilerimi Güncelle</button>
            </form>
        </article>
        <article class="profile-card">
            <h2>Hesap Güvenliği</h2>
            <ul class="account-insights">
                <li><strong>Bakiye:</strong> ₺<?= number_format((float)($user['balance'] ?? 0), 2) ?></li>
                <li><strong>Son Giriş IP:</strong> <?= sanitize($user['last_login_ip'] ?? 'Bilinmiyor') ?></li>
                <li><strong>Üyelik Tarihi:</strong> <?= !empty($user['created_at']) ? date('d.m.Y', strtotime($user['created_at'])) : '—' ?></li>
            </ul>
            <form action="/panel/password" method="post" class="stacked-form" novalidate>
                <?= csrf_field() ?>
                <div class="form-control<?= !empty($passwordErrors['current_password']) ? ' has-error' : '' ?>">
                    <label for="current_password">Mevcut Parola</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
                    <?php if (!empty($passwordErrors['current_password'])): ?>
                        <span class="error-text"><?= sanitize($passwordErrors['current_password'][0]) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-control<?= !empty($passwordErrors['password']) ? ' has-error' : '' ?>">
                    <label for="password">Yeni Parola</label>
                    <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
                    <?php if (!empty($passwordErrors['password'])): ?>
                        <span class="error-text"><?= sanitize($passwordErrors['password'][0]) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-control<?= !empty($passwordErrors['password_confirmation']) ? ' has-error' : '' ?>">
                    <label for="password_confirmation">Yeni Parola (Tekrar)</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                    <?php if (!empty($passwordErrors['password_confirmation'])): ?>
                        <span class="error-text"><?= sanitize($passwordErrors['password_confirmation'][0]) ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit">Parolamı Güncelle</button>
            </form>
        </article>
    </div>
</section>
