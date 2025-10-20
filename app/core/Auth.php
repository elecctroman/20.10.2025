<?php
namespace App\Core;

use App\Models\User;

class Auth
{
    protected static ?User $user = null;

    public static function user(): ?User
    {
        if (static::$user) {
            return static::$user;
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $userModel = new User();
        static::$user = $userModel->find((int) $_SESSION['user_id']);

        return static::$user;
    }

    public static function check(): bool
    {
        return static::user() !== null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = (bool) $user['is_admin'];

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['is_admin']);
        session_regenerate_id(true);
    }

    public static function requireAdmin(): void
    {
        if (!static::check() || empty($_SESSION['is_admin'])) {
            header('Location: /login');
            exit;
        }
    }
}
