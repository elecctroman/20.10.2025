<section aria-labelledby="search-title">
    <h1 id="search-title">Arama Sonuçları</h1>
    <?php if (empty($results)): ?>
        <p>Sonuç bulunamadı.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($results as $item): ?>
                <li><a href="/urun/<?= sanitize($item['slug']) ?>"><?= sanitize($item['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
