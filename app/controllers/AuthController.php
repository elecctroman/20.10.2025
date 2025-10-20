<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return $this->view('user/login', [], 'auth');
    }

    public function login()
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum anahtarı geçersiz.');
            return $this->redirect('/login');
        }

        if (!rate_limit('login_' . ($_POST['email'] ?? ''), 5, 300)) {
            session_flash('error', 'Çok fazla giriş denemesi. Lütfen daha sonra deneyin.');
            return $this->redirect('/login');
        }

        if (Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '')) {
            return $this->redirect('/panel');
        }

        session_flash('error', 'Giriş bilgileri hatalı.');
        return $this->redirect('/login');
    }

    public function showRegister()
    {
        return $this->view('user/register', [], 'auth');
    }

    public function register()
    {
        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum anahtarı geçersiz.');
            return $this->redirect('/register');
        }

        $errors = Validator::make($_POST, [
            'name' => 'required|min:2',
            'surname' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            session_flash('error', 'Formu kontrol ediniz.');
            return $this->redirect('/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($_POST['email'])) {
            session_flash('error', 'Bu e-posta ile kayıtlı bir hesap var.');
            return $this->redirect('/register');
        }

        $userModel->create([
            'name' => $_POST['name'],
            'surname' => $_POST['surname'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? null,
            'password' => $_POST['password'],
        ]);

        session_flash('success', 'Kayıt başarılı. Giriş yapabilirsiniz.');
        return $this->redirect('/login');
    }

    public function logout()
    {
        Auth::logout();
        return $this->redirect('/');
    }
}
