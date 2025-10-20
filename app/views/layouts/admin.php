<?php
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yönetim - <?= sanitize(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin">
<header class="admin-header">
    <nav aria-label="Yönetim menüsü">
        <a class="logo" href="/admin">Yönetim Paneli</a>
        <ul>
            <li><a href="/admin/orders">Siparişler</a></li>
            <li><a href="/admin/products">Ürünler</a></li>
            <li><a href="/admin/settings">Ayarlar</a></li>
            <li><a href="/">Siteye Dön</a></li>
        </ul>
    </nav>
</header>
<main>
    <?php $content(); ?>
</main>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
