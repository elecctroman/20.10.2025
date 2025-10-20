<?php
use App\Core\Auth;

$navCategories = [];
try {
    $categoryModel = new App\Models\Category();
    $navCategories = $categoryModel->all();
} catch (Throwable $e) {
    $navCategories = [];
}

$staticNav = [
    'PUBG', 'Valorant', 'Windows', 'Semrush', 'Adobe', 'Freepik', 'Canva', 'Shutterstock', 'Elementor'
];
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sanitize(config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
</head>
<body>
<header class="site-header" role="banner">
    <div class="site-header-inner" aria-label="Ana site üst bölümü">
        <a class="brand" href="/" aria-label="<?= sanitize(config('app.name')) ?> Anasayfa">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" role="img" aria-hidden="true">
                <defs>
                    <linearGradient id="logo-gradient" x1="0%" x2="100%" y1="0%" y2="100%">
                        <stop stop-color="#2ad0ff" offset="0"></stop>
                        <stop stop-color="#7f5dff" offset="0.5"></stop>
                        <stop stop-color="#c445ff" offset="1"></stop>
                    </linearGradient>
                </defs>
                <rect x="6" y="6" width="52" height="52" rx="16" fill="url(#logo-gradient)"/>
                <path d="M20 20h24v6H20zm0 10h24v6H20zm0 10h16v6H20z" fill="#05070f" opacity="0.9"/>
            </svg>
            <span><?= sanitize(config('app.name')) ?></span>
        </a>
        <div class="primary-nav">
            <div class="primary-nav-top">
                <nav aria-label="Kategori menüsü">
                    <ul>
                        <?php foreach ($staticNav as $label): ?>
                            <li><a href="/search?q=<?= urlencode($label) ?>"><?= sanitize($label) ?></a></li>
                        <?php endforeach; ?>
                        <?php foreach ($navCategories as $category): ?>
                            <li><a href="/kategori/<?= sanitize($category['slug']) ?>"><?= sanitize($category['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
            <form class="search-shell" role="search" method="get" action="/search">
                <label class="sr-only" for="site-search">Ürün ara</label>
                <input id="site-search" type="search" name="q" placeholder="Favori oyununuzu, uygulamayı ya da lisansı arayın" value="<?= sanitize($_GET['q'] ?? '') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79L20.5 21 21 20.5zM9.5 14c-2.48 0-4.5-2.02-4.5-4.5S7.02 5 9.5 5 14 7.02 14 9.5 11.98 14 9.5 14z"/></svg>
            </form>
        </div>
        <div class="header-actions" aria-label="Hızlı bağlantılar">
            <a class="header-action" href="/cart" aria-label="Sepet">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true"><path fill="currentColor" d="M7 4h-2l-1 2v2h2l3.6 7.59L8.25 17c-.3.55-.25 1.25.15 1.75S9 19.5 9.5 19.5H19v-2H9.91a.25.25 0 0 1-.24-.22l.03-.12.9-1.66h7.5c.75 0 1.41-.41 1.75-1.03l3-5.47-1.75-1-3 5.5H10.1l-.16-.32L7 4z"/></svg>
            </a>
            <?php if (Auth::check()): ?>
                <div class="user-menu">
                    <div>
                        <span><?= sanitize(Auth::user()['name'] ?? 'Müşteri') ?></span>
                        <small>₺<?= number_format((float) (Auth::user()['balance'] ?? 0), 2) ?></small>
                    </div>
                    <a href="/panel" aria-label="Hesabım">Panel</a>
                    <a href="/logout" aria-label="Çıkış">Çıkış</a>
                </div>
            <?php else: ?>
                <a class="header-action" href="/login" aria-label="Giriş Yap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true"><path fill="currentColor" d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
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
<footer class="site-footer" role="contentinfo">
    <div class="section-shell">
        <div class="footer-grid">
            <section>
                <h3>İletişim</h3>
                <ul>
                    <li>E-posta: <a href="mailto:support@epinmarket.test">support@epinmarket.test</a></li>
                    <li>Telefon: +90 212 000 00 00</li>
                    <li>WhatsApp: +90 555 000 00 00</li>
                </ul>
            </section>
            <section>
                <h3>Yasal</h3>
                <ul>
                    <li><a href="/sayfa/hakkimizda">Hakkımızda</a></li>
                    <li><a href="/sayfa/gizlilik">Gizlilik</a></li>
                    <li><a href="/sayfa/guvenlik">Güvenlik</a></li>
                    <li><a href="/sayfa/iade-degisim">İade &amp; Değişim</a></li>
                    <li><a href="/sayfa/kullanim-kosullari">Kullanım Koşulları</a></li>
                    <li><a href="/sayfa/cerez">Çerez Politikası</a></li>
                    <li><a href="/sayfa/mesafeli-satis">Mesafeli Satış</a></li>
                    <li><a href="/sayfa/kvkk">KVKK</a></li>
                </ul>
            </section>
            <section>
                <h3>Popüler Ürünler</h3>
                <ul>
                    <li><a href="/urun/pubg-uc-1800">PUBG 1800 UC</a></li>
                    <li><a href="/urun/valorant-vp-125">Valorant 125 VP</a></li>
                    <li><a href="/urun/windows-11-pro">Windows 11 Pro</a></li>
                    <li><a href="/urun/semrush-pro">Semrush Pro</a></li>
                    <li><a href="/urun/adobe-cc">Adobe Creative Cloud</a></li>
                </ul>
            </section>
        </div>
        <p class="footer-note">&copy; <?= date('Y') ?> <?= sanitize(config('app.name')) ?> • Neon hızında dijital teslimat.</p>
    </div>
</footer>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
