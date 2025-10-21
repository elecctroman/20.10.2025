# E-Pin Market

Modüler ve framework kullanmayan E-PİN / Lisans / Dijital hesap satış uygulaması. Apache + PHP 8 + MySQL ortamlarında cPanel uyumlu şekilde çalışır.

## Özellikler
- Ürünler için otomatik kod havuzu teslimatı
- Sepet, ödeme ve sipariş yönetimi
- Basit dosya önbelleği ve lazy-load destekli vitrin
- PayTR, Iyzico ve Mock ödeme sürücüleri
- Yönetici paneli ve kullanıcı paneli aynı kod tabanında
- CSRF, XSS koruması, parola hashing ve giriş rate-limit

## Kurulum
1. Projeyi sunucunuza kopyalayın ve `public/` dizinini web kök dizini olarak ayarlayın.
2. `app/config/database.php` dosyasını veritabanı bilgilerinizle güncelleyin.
3. `app/migrations/*.sql` dosyalarını MySQL sunucunuza uygulayın.
4. SMTP ayarlarınızı `app/config/mail.php` üzerinden yapılandırın.
5. Gerekirse `app/config/payment.php` içerisindeki ödeme bilgilerini düzenleyin.

## Cron İşleri
- `cron/order_auto_delivery.php`: Ödenmiş siparişler için kod havuzundan otomatik teslimat yapar.
- `cron/stock_low_alert.php`: Stok kritik seviyeye düştüğünde e-posta ile bilgilendirir.

## Geliştirme
- Ek dil desteği için `lang/` klasörüne yeni JSON dosyası ekleyin.
- Yeni kontrolör veya model eklerken `app/controllers` ve `app/models` yapısını takip edin.
