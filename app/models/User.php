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
        $stmt = database()->prepare('INSERT INTO users (name, email, password, balance, is_admin, created_at, updated_at) VALUES (:name, :email, :password, 0, 0, NOW(), NOW())');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);

        return (int) database()->lastInsertId();
    }
}
