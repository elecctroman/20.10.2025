<article aria-labelledby="page-title">
    <h1 id="page-title"><?= sanitize($page['title']) ?></h1>
    <div class="page-content"><?= nl2br(sanitize($page['content'])) ?></div>
</article>
