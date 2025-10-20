<article aria-labelledby="post-title">
    <h1 id="post-title"><?= sanitize($post['title']) ?></h1>
    <?php if (!empty($post['published_at'])): ?>
        <time datetime="<?= sanitize($post['published_at']) ?>"><?= date('d.m.Y', strtotime($post['published_at'])) ?></time>
    <?php endif; ?>
    <div class="post-content"><?= sanitize_html($post['content_html']) ?></div>
</article>
