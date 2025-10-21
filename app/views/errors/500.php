<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bir Hata Oluştu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/base.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, rgba(42,208,255,.18), rgba(7,11,25,1));
            color: #f5f7ff;
        }
        .error-card {
            max-width: 520px;
            padding: 3rem 2.5rem;
            border-radius: 24px;
            background: rgba(7, 11, 25, 0.8);
            box-shadow: 0 28px 80px rgba(10, 15, 35, 0.45);
            text-align: center;
        }
        .error-card h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            background: linear-gradient(120deg, #2ad0ff, #7f5dff, #c445ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .error-card p {
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .error-card a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.75rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #2ad0ff, #7f5dff);
            color: #05070f;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 12px 30px rgba(42, 208, 255, 0.3);
        }
        .error-card a:hover {
            box-shadow: 0 16px 36px rgba(127, 93, 255, 0.45);
        }
    </style>
</head>
<body>
    <main class="error-card" role="alert">
        <h1>Beklenmeyen Bir Hata Oluştu</h1>
        <p><?= sanitize($message ?? 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.') ?></p>
        <a href="/">Anasayfaya Dön</a>
    </main>
</body>
</html>
