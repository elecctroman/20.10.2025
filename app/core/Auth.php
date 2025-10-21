<?php
namespace App\Core;

use App\Models\User;

class Auth
{
    protected static ?array $user = null;

    public static function user(): ?array
    {
        if (static::$user) {
            return static::$user;
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $userModel = new User();
        static::$user = $userModel->find((int) $_SESSION['user_id']);

        return static::$user ?: null;
    }

    public static function check(): bool
    {
        return static::user() !== null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || empty($user['is_active']) || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        database()->prepare('UPDATE users SET last_login_ip = :ip, updated_at = NOW() WHERE id = :id')->execute([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'id' => $user['id'],
        ]);

        static::$user = $user;

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['role']);
        static::$user = null;
        session_regenerate_id(true);
    }

    public static function sync(): void
    {
        if (!isset($_SESSION['user_id'])) {
            static::$user = null;
            return;
        }

        $userModel = new User();
        static::$user = $userModel->find((int) $_SESSION['user_id']);
    }

    public static function requireAdmin(): void
    {
        $user = static::user();
        if (!$user || !in_array($user['role'], ['admin', 'staff'], true)) {
            header('Location: /login');
            exit;
        }
    }
}
