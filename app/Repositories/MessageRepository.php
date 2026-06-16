<?php
/**
 * Project: QazJumys
 * File: MessageRepository.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Stores project messages between publishers and accepted or bidding freelancers.
 * RU: Хранит сообщения по проектам между авторами и исполнителями.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

final class MessageRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Creates a project message.
     * RU: Создает сообщение по проекту.
     *
     * @param int $projectId Project id / ID проекта
     * @param int|null $proposalId Proposal id / ID отклика
     * @param int $senderId Sender id / ID отправителя
     * @param int $receiverId Receiver id / ID получателя
     * @param string $body Message body / Текст сообщения
     * @return int
     */
    public function create(int $projectId, ?int $proposalId, int $senderId, int $receiverId, string $body): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO messages (project_id, proposal_id, sender_id, receiver_id, body, is_read, created_at)
             VALUES (:project_id, :proposal_id, :sender_id, :receiver_id, :body, 0, NOW())'
        );
        $statement->execute([
            'project_id' => $projectId,
            'proposal_id' => $proposalId,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'body' => $body,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Returns recent messages visible to the user.
     * RU: Возвращает последние сообщения, доступные пользователю.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function recentForUser(int $userId, int $limit = 12): array
    {
        $statement = $this->pdo->prepare(
            'SELECT m.*, p.title AS project_title, sender.name AS sender_name, receiver.name AS receiver_name
             FROM messages m
             INNER JOIN projects p ON p.id = m.project_id
             INNER JOIN users sender ON sender.id = m.sender_id
             INNER JOIN users receiver ON receiver.id = m.receiver_id
             WHERE m.sender_id = :sender_user_id OR m.receiver_id = :receiver_user_id
             ORDER BY m.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('sender_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('receiver_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Counts unread messages for the user.
     * RU: Считает непрочитанные сообщения пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @return int
     */
    public function unreadCount(int $userId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND is_read = 0');
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }
}
