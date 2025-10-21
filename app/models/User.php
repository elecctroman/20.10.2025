<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function find(int $id): ?array
    {
        $stmt = $this->query('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = database()->prepare(
            'INSERT INTO users (role, name, surname, email, phone, password_hash, balance, is_active, created_at, updated_at) VALUES (:role, :name, :surname, :email, :phone, :password_hash, :balance, :is_active, NOW(), NOW())'
        );
        $stmt->execute([
            'role' => $data['role'] ?? 'customer',
            'name' => $data['name'],
            'surname' => $data['surname'] ?? '',
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'balance' => $data['balance'] ?? 0.00,
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return (int) database()->lastInsertId();
    }

    public function incrementBalance(int $userId, float $amount): void
    {
        $stmt = database()->prepare('UPDATE users SET balance = balance + :amount, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['amount' => $amount, 'id' => $userId]);
    }

    public function updateProfile(int $userId, array $data): void
    {
        $stmt = database()->prepare('UPDATE users SET name = :name, surname = :surname, email = :email, phone = :phone, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'id' => $userId,
        ]);
    }

    public function updatePassword(int $userId, string $password): void
    {
        $stmt = database()->prepare('UPDATE users SET password_hash = :password, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);
    }
}
