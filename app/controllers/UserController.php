<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Core\CSRF;
use App\Core\Validator;
use App\Services\PaymentGateway;

class UserController extends Controller
{
    public function profile()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        return $this->view('user/profile', ['user' => Auth::user()]);
    }

    public function updateProfile()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/panel');
        }

        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'surname' => trim($_POST['surname'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ];

        $errors = Validator::make($payload, [
            'name' => 'required|min:2|max:120',
            'surname' => 'required|min:2|max:120',
            'email' => 'required|email',
        ]);

        $current = Auth::user();
        $userModel = new User();

        if ($payload['email'] !== ($current['email'] ?? '')) {
            $existing = $userModel->findByEmail($payload['email']);
            if ($existing && (int) $existing['id'] !== (int) $current['id']) {
                $errors['email'][] = 'Bu e-posta adresi kullanımda.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['_form_errors']['profile'] = $errors;
            session_flash('error', 'Bilgilerinizi kontrol ediniz.');
            return $this->redirect('/panel');
        }

        $userModel->updateProfile((int) $current['id'], $payload);
        Auth::sync();
        unset($_SESSION['_form_errors']['profile']);
        session_flash('success', 'Profiliniz güncellendi.');

        return $this->redirect('/panel');
    }

    public function orders()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $orderModel = new Order();
        return $this->view('user/orders', [
            'orders' => $orderModel->byUser(Auth::user()['id']),
            'activeOrder' => null,
            'activeOrderItems' => [],
            'user' => Auth::user(),
        ]);
    }

    public function balance()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        return $this->view('user/balance', [
            'user' => Auth::user(),
        ]);
    }

    public function topup()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/panel/balance');
        }

        if (empty($_POST['payment_method'])) {
            session_flash('error', 'Ödeme yöntemini seçiniz.');
            return $this->redirect('/panel/balance');
        }

        if (!is_numeric($_POST['amount'] ?? null)) {
            session_flash('error', 'Geçerli bir tutar giriniz.');
            return $this->redirect('/panel/balance');
        }

        $amount = (float) $_POST['amount'];
        if ($amount < 10) {
            session_flash('error', 'Geçerli bir tutar giriniz.');
            return $this->redirect('/panel/balance');
        }

        $gateway = new PaymentGateway();
        $result = $gateway->charge($_POST['payment_method'], [
            'amount' => $amount,
            'email' => Auth::user()['email'],
            'description' => 'Cüzdan Yükleme',
        ]);

        if (!$result['success']) {
            session_flash('error', $result['message'] ?? 'Ödeme başarısız.');
            return $this->redirect('/panel/balance');
        }

        $orderModel = new Order();
        $orderId = $orderModel->create([
            'user_id' => Auth::user()['id'],
            'total' => $amount,
            'status' => 'delivered',
            'customer_note' => 'wallet_topup',
            'admin_note' => 'wallet_topup',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        database()->prepare('INSERT INTO payments (order_id, gateway, status, amount, txn_id, payload, created_at) VALUES (:order_id, :gateway, :status, :amount, :txn_id, :payload, NOW())')
            ->execute([
                'order_id' => $orderId,
                'gateway' => $_POST['payment_method'],
                'status' => 'success',
                'amount' => $amount,
                'txn_id' => $result['transaction_id'] ?? null,
                'payload' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ]);

        $userModel = new User();
        $userModel->incrementBalance(Auth::user()['id'], $amount);
        Auth::sync();

        audit('wallet_topup', ['amount' => $amount, 'order_id' => $orderId]);

        session_flash('success', '₺' . number_format($amount, 2) . ' bakiyenize eklendi.');
        return $this->redirect('/panel/balance');
    }

    public function tickets()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $ticketModel = new Ticket();
        return $this->view('user/tickets', [
            'tickets' => $ticketModel->byUser(Auth::user()['id']),
            'user' => Auth::user(),
        ]);
    }

    public function sessions()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $user = Auth::user();
        $logs = database()->prepare('SELECT * FROM audit_logs WHERE user_id = :id ORDER BY created_at DESC LIMIT 10');
        $logs->execute(['id' => $user['id']]);

        return $this->view('user/sessions', [
            'user' => $user,
            'logs' => $logs->fetchAll(),
        ]);
    }

    public function updatePassword()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/panel');
        }

        $current = Auth::user();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirmation'] ?? '';

        $errors = [];

        if (!password_verify($currentPassword, $current['password_hash'] ?? '')) {
            $errors['current_password'][] = 'Mevcut parolanız doğrulanamadı.';
        }

        if (mb_strlen($newPassword) < 8) {
            $errors['password'][] = 'Yeni parola en az 8 karakter olmalıdır.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors['password_confirmation'][] = 'Parolalar eşleşmiyor.';
        }

        if (!empty($errors)) {
            $_SESSION['_form_errors']['password'] = $errors;
            session_flash('error', 'Parola güncellenemedi.');
            return $this->redirect('/panel');
        }

        $userModel = new User();
        $userModel->updatePassword((int) $current['id'], $newPassword);
        Auth::sync();
        unset($_SESSION['_form_errors']['password']);

        session_flash('success', 'Parolanız güncellendi.');
        return $this->redirect('/panel');
    }
}
