<nav class="user-tabs" aria-label="Hesap sekmeleri">
    <?php
    $tabs = [
        ['/panel', 'Profil'],
        ['/panel/orders', 'Siparişler'],
        ['/panel/balance', 'Bakiyem'],
        ['/panel/tickets', 'Destek Talepleri'],
        ['/panel/sessions', 'Son Oturumlar'],
    ];
    $current = strtok($_SERVER['REQUEST_URI'] ?? '/panel', '?');
    foreach ($tabs as [$href, $label]):
        $active = str_starts_with($current, $href);
    ?>
        <a href="<?= $href ?>" class="<?= $active ? 'active' : '' ?>"><?= sanitize($label) ?></a>
    <?php endforeach; ?>
</nav>
