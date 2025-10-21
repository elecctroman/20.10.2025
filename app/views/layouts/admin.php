<?php
use App\Core\Auth;
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yönetim - <?= sanitize(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin">
<header class="admin-header" role="banner">
    <div class="admin-header-inner">
        <a class="logo" href="/admin">Neon Panel</a>
        <nav aria-label="Yönetim menüsü">
            <ul>
                <li><a href="/admin">Gösterge Paneli</a></li>
                <li><a href="/admin/orders">Siparişler</a></li>
                <li><a href="/admin/products">Ürünler</a></li>
                <li><a href="/admin/settings">Ayarlar</a></li>
            </ul>
        </nav>
        <div class="admin-user">
            <span><?= sanitize(Auth::user()['name'] ?? 'Admin') ?></span>
            <a href="/" class="secondary">Mağaza</a>
        </div>
    </div>
</header>
<main>
    <?php $content(); ?>
</main>
<script src="<?= asset('js/chart.js') ?>" defer></script>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
