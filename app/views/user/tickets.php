<section class="section-shell" aria-labelledby="tickets-title">
    <?php include __DIR__ . '/partials/tabs.php'; ?>
    <header class="section-title">
        <h1 id="tickets-title">Destek Taleplerim</h1>
        <span>Destek ekibimiz 7/24 yanınızda</span>
    </header>
    <p><a class="button" href="/ticket/create">Yeni Talep Oluştur</a></p>
    <?php if (empty($tickets)): ?>
        <p>Henüz destek talebi bulunmuyor.</p>
    <?php else: ?>
        <div class="blog-grid">
            <?php foreach ($tickets as $ticket): ?>
                <article class="blog-card">
                    <h2><?= sanitize($ticket['subject']) ?></h2>
                    <p>Durum: <?= sanitize(strtoupper($ticket['status'])) ?></p>
                    <p>Oluşturulma: <?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
