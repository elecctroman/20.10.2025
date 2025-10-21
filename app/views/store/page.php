<article class="section-shell panel-shell" aria-labelledby="page-title">
    <header class="section-title">
        <h1 id="page-title"><?= sanitize($page['title']) ?></h1>
        <span>Dijital mağaza dokümanı</span>
    </header>
    <div class="profile-card">
        <div class="page-content"><?= sanitize_html($page['content_html']) ?></div>
    </div>
</article>
