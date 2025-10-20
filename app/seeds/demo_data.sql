INSERT INTO users (role, name, surname, email, phone, password_hash, balance, is_active)
VALUES
('admin', 'Admin', 'Kullanıcı', 'admin@example.com', '+900000000000', '$2y$10$abcdefghijklmnopqrstuv', 0, 1),
('customer', 'Müşteri', 'Örnek', 'customer@example.com', '+900000000001', '$2y$10$abcdefghijklmnopqrstuv', 25.50, 1);
