<section class="section-shell" aria-labelledby="profile-title">
    <?php include __DIR__ . '/partials/tabs.php'; ?>
    <header class="section-title">
        <h1 id="profile-title">Profilim</h1>
        <span>Hesap ve iletişim bilgileriniz</span>
    </header>
    <div class="profile-grid">
        <article class="profile-card">
            <h2>Kişisel Bilgiler</h2>
            <p><strong>Ad Soyad:</strong> <?= sanitize(($user['name'] ?? '') . ' ' . ($user['surname'] ?? '')) ?></p>
            <p><strong>E-posta:</strong> <?= sanitize($user['email'] ?? '') ?></p>
            <?php if (!empty($user['phone'])): ?>
                <p><strong>Telefon:</strong> <?= sanitize($user['phone']) ?></p>
            <?php endif; ?>
        </article>
        <article class="profile-card">
            <h2>Hesap Durumu</h2>
            <p><strong>Bakiye:</strong> ₺<?= number_format((float)($user['balance'] ?? 0), 2) ?></p>
            <p><strong>Son Giriş IP:</strong> <?= sanitize($user['last_login_ip'] ?? 'Bilinmiyor') ?></p>
            <p><strong>Üyelik Tarihi:</strong> <?= !empty($user['created_at']) ? date('d.m.Y', strtotime($user['created_at'])) : '—' ?></p>
        </article>
    </div>
</section>
