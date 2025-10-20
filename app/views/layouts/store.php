<?php
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sanitize(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
</head>
<body>
<header class="site-header">
    <nav aria-label="Ana menü">
        <a class="logo" href="/">E-Pin Market</a>
        <ul>
            <li><a href="/">Anasayfa</a></li>
            <li><a href="/cart">Sepet</a></li>
            <li><a href="/blog">Blog</a></li>
            <?php if (\App\Core\Auth::check()): ?>
                <li><a href="/panel">Hesabım</a></li>
                <li><a href="/logout">Çıkış</a></li>
            <?php else: ?>
                <li><a href="/login">Giriş</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main id="content">
    <?php if ($flash = session_flash('success')): ?>
        <div class="alert success" role="alert"><?= sanitize($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = session_flash('error')): ?>
        <div class="alert error" role="alert"><?= sanitize($flash) ?></div>
    <?php endif; ?>
    <?php $content(); ?>
</main>
<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> E-Pin Market</p>
</footer>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
