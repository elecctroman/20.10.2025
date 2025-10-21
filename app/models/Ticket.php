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
        $stmt = database()->prepare(
            'INSERT INTO tickets (user_id, subject, status, created_at, updated_at) VALUES (:user_id, :subject, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'user_id' => $data['user_id'],
            'subject' => $data['subject'],
            'status' => $data['status'] ?? 'open',
        ]);

        $ticketId = (int) database()->lastInsertId();

        if (!empty($data['message_html'])) {
            $messageStmt = database()->prepare(
                'INSERT INTO ticket_messages (ticket_id, user_id, message_html, created_at) VALUES (:ticket_id, :user_id, :message_html, NOW())'
            );
            $messageStmt->execute([
                'ticket_id' => $ticketId,
                'user_id' => $data['user_id'],
                'message_html' => sanitize_html($data['message_html']),
            ]);
        }

        return $ticketId;
    }
}
