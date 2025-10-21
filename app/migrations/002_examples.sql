INSERT INTO categories (name, slug, description, position, is_active)
VALUES
('Oyun Kodları', 'oyun-kodlari', 'Popüler oyun ve dijital ürün kodları', 1, 1),
('Yazılım Lisansları', 'yazilim-lisanslari', 'Profesyonel yazılım lisansları', 2, 1);

INSERT INTO products (category_id, name, slug, short_desc, long_desc, image, is_active, delivery_mode, requires_input, input_label, min_qty, max_qty)
VALUES
(1, 'Valorant VP 125', 'valorant-vp-125', 'Valorant için 125 VP dijital kod', 'Valorant hesabınıza VP yüklemesi yapmak için bu kodu kullanabilirsiniz.', NULL, 1, 'auto', 0, NULL, 1, 5),
(2, 'Windows 11 Pro Lisansı', 'windows-11-pro', 'Windows 11 Pro OEM anahtarı', 'Windows 11 Pro işletim sistemi için orijinal OEM lisans anahtarı.', NULL, 1, 'manual', 1, 'Kurulum E-postası', 1, 1);

INSERT INTO variants (product_id, name, price, compare_at_price, stock_visible, is_active)
VALUES
(1, 'Valorant VP 125', 59.90, NULL, 10, 1),
(2, 'Windows 11 Pro Lisansı', 349.00, 429.00, 3, 1);

INSERT INTO stock_codes (product_id, variant_id, code)
VALUES
(1, 1, 'VP-AAA-BBB-CCC'),
(1, 1, 'VP-DDD-EEE-FFF'),
(2, 2, 'WIN11-XXXXX-YYYYY-ZZZZZ');

INSERT INTO blog_posts (title, slug, cover, excerpt, content_html, is_active, published_at)
VALUES
('E-Pin Nedir?', 'e-pin-nedir', NULL, 'E-Pin kavramına kısa bir giriş.', '<p>E-Pin dijital ürünlerin hızlı ve güvenli teslimi için kullanılan kodlardır.</p>', 1, NOW());

INSERT INTO pages (title, slug, content_html, is_active)
VALUES
('Hakkımızda', 'hakkimizda', '<p>Dijital ürünlerde güvenilir adresiniz.</p>', 1);
