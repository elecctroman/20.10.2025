<article class="section-shell panel-shell" aria-labelledby="post-title">
    <header class="section-title">
        <h1 id="post-title"><?= sanitize($post['title']) ?></h1>
        <?php if (!empty($post['published_at'])): ?>
            <span><?= date('d.m.Y', strtotime($post['published_at'])) ?></span>
        <?php endif; ?>
    </header>
    <div class="profile-card">
        <div class="post-content"><?= sanitize_html($post['content_html']) ?></div>
    </div>
</article>
