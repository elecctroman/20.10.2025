<section aria-labelledby="tickets-title">
    <h1 id="tickets-title">Destek Taleplerim</h1>
    <a class="button" href="/ticket/create">Yeni Talep</a>
    <?php if (empty($tickets)): ?>
        <p>Henüz destek talebi oluşturmadınız.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tickets as $ticket): ?>
                <li>
                    <strong><?= sanitize($ticket['subject']) ?></strong>
                    <span><?= sanitize($ticket['status']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
