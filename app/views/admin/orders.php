<section aria-labelledby="admin-orders-title">
    <h1 id="admin-orders-title">Sipariş Yönetimi</h1>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Kullanıcı</th>
            <th>Tutar</th>
            <th>Durum</th>
            <th>Tarih</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= (int) $order['id'] ?></td>
                <td><?= $order['user_id'] ? sanitize((string) $order['user_id']) : 'Misafir' ?></td>
                <td>₺<?= number_format($order['total'], 2) ?></td>
                <td><?= sanitize($order['status']) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
