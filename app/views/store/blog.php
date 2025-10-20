<section aria-labelledby="blog-title">
    <h1 id="blog-title">Blog</h1>
    <ul class="blog-posts">
        <?php foreach ($posts as $post): ?>
            <li>
                <article>
                    <h2><a href="/blog/<?= sanitize($post['slug']) ?>"><?= sanitize($post['title']) ?></a></h2>
                    <?php $excerpt = $post['excerpt'] ?? strip_tags($post['content_html'] ?? ''); ?>
                    <p><?= sanitize(mb_substr($excerpt, 0, 160)) ?><?= mb_strlen($excerpt) > 160 ? '…' : '' ?></p>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
