<section aria-labelledby="blog-title">
    <h1 id="blog-title">Blog</h1>
    <ul class="blog-posts">
        <?php foreach ($posts as $post): ?>
            <li>
                <article>
                    <h2><a href="/blog/<?= sanitize($post['slug']) ?>"><?= sanitize($post['title']) ?></a></h2>
                    <p><?= sanitize(mb_substr($post['content'], 0, 160)) ?>...</p>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
