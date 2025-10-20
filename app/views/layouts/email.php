<?php
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sanitize(config('app.name')) ?> Bildirimi</title>
</head>
<body style="font-family: Arial, sans-serif; color:#222;">
<div style="max-width:600px;margin:0 auto;padding:24px;">
    <?php $content(); ?>
    <p style="font-size:12px;color:#666;">Bu e-posta otomatik gönderilmiştir. Lütfen yanıtlamayınız.</p>
</div>
</body>
</html>
