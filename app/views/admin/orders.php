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
                <td><?= sanitize($order['user_id']) ?></td>
                <td>₺<?= number_format($order['total_amount'], 2) ?></td>
                <td><?= sanitize($order['status']) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
