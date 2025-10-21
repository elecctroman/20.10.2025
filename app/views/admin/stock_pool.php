<section class="admin-section" aria-labelledby="stock-title">
    <header class="admin-header">
        <h1 id="stock-title">Stok Havuzu</h1>
        <p>Otomatik teslim kodlarını yönetin, CSV veya metin ile toplu yükleme yapın.</p>
    </header>
    <form method="get" class="filter-bar">
        <label>
            Ürün
            <select name="product_id" onchange="this.form.submit()">
                <option value="">Tümü</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>" <?= (isset($filters['product_id']) && (int) $filters['product_id'] === (int) $product['id']) ? 'selected' : '' ?>><?= sanitize($product['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Varyant
            <select name="variant_id" onchange="this.form.submit()">
                <option value="">Tümü</option>
                <option value="none" <?= ($filters['variant_id'] ?? '') === 'none' ? 'selected' : '' ?>>Varyantsız</option>
                <?php if (!empty($variants)): ?>
                    <?php foreach ($variants as $variant): ?>
                        <option value="<?= (int) $variant['id'] ?>" <?= (isset($filters['variant_id']) && (int) $filters['variant_id'] === (int) $variant['id']) ? 'selected' : '' ?>><?= sanitize($variant['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </label>
        <label>
            Durum
            <select name="status" onchange="this.form.submit()">
                <option value="">Tümü</option>
                <option value="available" <?= ($filters['status'] ?? '') === 'available' ? 'selected' : '' ?>>Bekleyen</option>
                <option value="assigned" <?= ($filters['status'] ?? '') === 'assigned' ? 'selected' : '' ?>>Atanan</option>
                <option value="consumed" <?= ($filters['status'] ?? '') === 'consumed' ? 'selected' : '' ?>>Tükenen</option>
            </select>
        </label>
    </form>
    <div class="stock-grid">
        <section class="stock-upload">
            <h2>Kod Yükle</h2>
            <form method="post" action="/admin/stock/upload" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <label>Ürün
                    <select name="product_id" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product['id'] ?>"><?= sanitize($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Varyant
                    <input type="number" name="variant_id" min="1" placeholder="Opsiyonel">
                </label>
                <label>Kod Listesi
                    <textarea name="bulk_codes" rows="6" placeholder="Her satıra bir kod"></textarea>
                </label>
                <label>CSV / TXT Yükle
                    <input type="file" name="csv_file" accept=".csv,.txt">
                </label>
                <button type="submit">Stoklara Ekle</button>
            </form>
        </section>
        <section class="stock-table">
            <h2>Havuz Durumu</h2>
            <table>
                <thead>
                <tr>
                    <th scope="col">Kod</th>
                    <th scope="col">Ürün</th>
                    <th scope="col">Varyant</th>
                    <th scope="col">Durum</th>
                    <th scope="col">Güncelleme</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($codes)): ?>
                    <tr><td colspan="5">Kayıt bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($codes as $code): ?>
                        <tr>
                            <td><?= sanitize($code['code']) ?></td>
                            <td><?= sanitize($code['product_name']) ?></td>
                            <td><?= sanitize($code['variant_name'] ?? '-') ?></td>
                            <td><?= $code['is_used'] ? 'Kullanıldı' : 'Bekliyor' ?></td>
                            <td><?= $code['used_at'] ? date('d.m.Y H:i', strtotime($code['used_at'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</section>
