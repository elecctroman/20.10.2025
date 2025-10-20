INSERT INTO categories (name, slug, description) VALUES
('Oyun Kodları', 'oyun-kodlari', 'Popüler oyun kodları'),
('Yazılım Lisansları', 'yazilim-lisanslari', 'Profesyonel yazılım lisansları');

INSERT INTO products (category_id, name, slug, description, price, auto_delivery, status) VALUES
(1, 'Valorant VP 125', 'valorant-vp-125', 'Riot Games Valorant VP kodu.', 59.90, 1, 1),
(2, 'Windows 11 Pro Lisansı', 'windows-11-pro', 'Microsoft Windows 11 Pro OEM lisansı.', 349.00, 0, 1);

INSERT INTO stock_codes (product_id, code) VALUES
(1, 'VP-AAA-BBB-CCC'),
(1, 'VP-DDD-EEE-FFF'),
(2, 'WIN11-XXXXX-YYYYY-ZZZZZ');

INSERT INTO posts (title, slug, content, status, published_at) VALUES
('E-Pin Nedir?', 'e-pin-nedir', 'E-Pin dijital bir üründür...', 1, NOW());
