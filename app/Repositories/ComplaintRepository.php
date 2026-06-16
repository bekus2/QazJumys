<?php
/**
 * Project: QazJumys
 * File: ComplaintRepository.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Stores user complaints for owner moderation.
 * RU: Хранит жалобы пользователей для модерации владельцем.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

final class ComplaintRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Creates a moderation complaint.
     * RU: Создает жалобу для модерации.
     *
     * @param array<string, mixed> $data Complaint data / Данные жалобы
     * @return int
     */
    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO complaints (reporter_id, reported_user_id, project_id, proposal_id, subject, body, status, created_at, updated_at)
             VALUES (:reporter_id, :reported_user_id, :project_id, :proposal_id, :subject, :body, "open", NOW(), NOW())'
        );
        $statement->execute([
            'reporter_id' => (int) $data['reporter_id'],
            'reported_user_id' => $data['reported_user_id'] ?: null,
            'project_id' => $data['project_id'] ?: null,
            'proposal_id' => $data['proposal_id'] ?: null,
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Returns complaints created by the user.
     * RU: Возвращает жалобы, созданные пользователем.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function byReporter(int $userId, int $limit = 8): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.*, reported.name AS reported_name, p.title AS project_title
             FROM complaints c
             LEFT JOIN users reported ON reported.id = c.reported_user_id
             LEFT JOIN projects p ON p.id = c.project_id
             WHERE c.reporter_id = :user_id
             ORDER BY c.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
