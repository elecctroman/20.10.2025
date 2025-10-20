<section class="section-shell" aria-labelledby="blog-title">
    <header class="section-title">
        <h1 id="blog-title">Blog</h1>
        <span>Güncel ipuçları ve kampanyalar</span>
    </header>
    <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
            <article class="blog-card">
                <h2><a href="/blog/<?= sanitize($post['slug']) ?>"><?= sanitize($post['title']) ?></a></h2>
                <?php $excerpt = $post['excerpt'] ?? strip_tags($post['content_html'] ?? ''); ?>
                <p><?= sanitize(mb_substr($excerpt, 0, 160)) ?><?= mb_strlen($excerpt) > 160 ? '…' : '' ?></p>
                <?php if (!empty($post['published_at'])): ?>
                    <time datetime="<?= sanitize($post['published_at']) ?>"><?= date('d.m.Y', strtotime($post['published_at'])) ?></time>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
