<?php
namespace App\Models;

use App\Core\Model;

class Ticket extends Model
{
    public function byUser(int $userId): array
    {
        $stmt = $this->query('SELECT * FROM tickets WHERE user_id = :user_id ORDER BY updated_at DESC', ['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = database()->prepare('INSERT INTO tickets (user_id, subject, message, status, created_at, updated_at) VALUES (:user_id, :subject, :message, :status, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => $data['status'] ?? 'open',
        ]);

        return (int) database()->lastInsertId();
    }
}
