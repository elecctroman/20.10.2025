<article aria-labelledby="post-title">
    <h1 id="post-title"><?= sanitize($post['title']) ?></h1>
    <time datetime="<?= sanitize($post['published_at']) ?>"><?= date('d.m.Y', strtotime($post['published_at'])) ?></time>
    <div class="post-content"><?= nl2br(sanitize($post['content'])) ?></div>
</article>
