<article aria-labelledby="page-title">
    <h1 id="page-title"><?= sanitize($page['title']) ?></h1>
    <div class="page-content"><?= sanitize_html($page['content_html']) ?></div>
</article>
