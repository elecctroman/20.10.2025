<section aria-labelledby="profile-title">
    <h1 id="profile-title">Profilim</h1>
    <dl>
        <dt>Ad Soyad</dt>
        <dd><?= sanitize(($user['name'] ?? '') . ' ' . ($user['surname'] ?? '')) ?></dd>
        <dt>E-posta</dt>
        <dd><?= sanitize($user['email'] ?? '') ?></dd>
        <?php if (!empty($user['phone'])): ?>
            <dt>Telefon</dt>
            <dd><?= sanitize($user['phone']) ?></dd>
        <?php endif; ?>
        <dt>Bakiye</dt>
        <dd>₺<?= number_format((float)($user['balance'] ?? 0), 2) ?></dd>
    </dl>
</section>
