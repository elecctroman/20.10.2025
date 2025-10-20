<section aria-labelledby="cart-title" class="cart">
    <h1 id="cart-title">Sepet</h1>
    <?php if (empty($cart)): ?>
        <p>Sepetiniz boş.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th scope="col">Ürün</th>
                <th scope="col">Adet</th>
                <th scope="col">Fiyat</th>
                <th scope="col">İşlem</th>
            </tr>
            </thead>
            <tbody>
            <?php $total = 0; foreach ($cart as $item): $total += ($item['product']['price'] ?? 0) * $item['quantity']; ?>
                <tr>
                    <td>
                        <?= sanitize($item['product']['name']) ?>
                        <?php if (!empty($item['input_value'])): ?>
                            <div class="input-note">Bilgi: <?= sanitize($item['input_value']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td>₺<?= number_format($item['product']['price'] ?? 0, 2) ?></td>
                    <td><a href="/cart/remove/<?= (int) $item['product']['id'] ?>">Kaldır</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="cart-total">Toplam: ₺<?= number_format($total, 2) ?></p>
        <a class="button" href="/checkout">Ödemeye Geç</a>
    <?php endif; ?>
</section>
