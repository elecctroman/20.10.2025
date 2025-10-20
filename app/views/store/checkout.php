<section aria-labelledby="checkout-title">
    <h1 id="checkout-title">Ödeme</h1>
    <form method="post" action="/checkout">
        <?= csrf_field() ?>
        <fieldset>
            <legend>İletişim Bilgileri</legend>
            <label for="email">E-posta</label>
            <input id="email" name="email" type="email" required>
            <label for="customer-note">Notunuz</label>
            <textarea id="customer-note" name="customer_note" rows="3" maxlength="500"></textarea>
        </fieldset>
        <fieldset>
            <legend>Ödeme Yöntemi</legend>
            <label><input type="radio" name="payment_method" value="mock" checked> Mock</label>
            <label><input type="radio" name="payment_method" value="paytr"> PayTR</label>
            <label><input type="radio" name="payment_method" value="iyzico"> Iyzico</label>
        </fieldset>
        <button type="submit">Ödemeyi Tamamla</button>
    </form>
</section>
