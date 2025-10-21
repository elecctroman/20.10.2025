<section class="section-shell" aria-labelledby="sessions-title">
    <?php include __DIR__ . '/partials/tabs.php'; ?>
    <header class="section-title">
        <h1 id="sessions-title">Son Oturumlar</h1>
        <span>Hesabınıza erişim geçmişi</span>
    </header>
    <?php if (empty($logs)): ?>
        <p>Henüz oturum kaydı bulunamadı.</p>
    <?php else: ?>
        <div class="blog-grid">
            <?php foreach ($logs as $log): ?>
                <article class="blog-card">
                    <h2><?= sanitize($log['action'] ?? 'Oturum') ?></h2>
                    <p><strong>Tarih:</strong> <?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></p>
                    <?php if (!empty($log['meta_json'])): ?>
                        <?php $meta = json_decode($log['meta_json'], true) ?: []; ?>
                        <?php if (!empty($meta['ip'])): ?>
                            <p><strong>IP:</strong> <?= sanitize($meta['ip']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($meta['agent'])): ?>
                            <p><strong>Tarayıcı:</strong> <?= sanitize($meta['agent']) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
