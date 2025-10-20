<?php
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş - <?= sanitize(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
</head>
<body class="auth">
<main class="auth-container">
    <?php if ($flash = session_flash('error')): ?>
        <div class="alert error" role="alert"><?= sanitize($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = session_flash('success')): ?>
        <div class="alert success" role="alert"><?= sanitize($flash) ?></div>
    <?php endif; ?>
    <?php $content(); ?>
</main>
</body>
</html>
