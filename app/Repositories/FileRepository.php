<?php
/**
 * Project: QazJumys
 * File: FileRepository.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Stores metadata for protected project uploads.
 * RU: Хранит метаданные защищенных файлов проекта.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

final class FileRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Saves metadata after a validated file upload.
     * RU: Сохраняет метаданные после проверенной загрузки файла.
     *
     * @param array<string, mixed> $data File metadata / Метаданные файла
     * @return int
     */
    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO project_files (
                project_id, proposal_id, uploader_id, original_name, stored_name, mime_type, file_size, visibility, created_at
             )
             VALUES (
                :project_id, :proposal_id, :uploader_id, :original_name, :stored_name, :mime_type, :file_size, :visibility, NOW()
             )'
        );
        $statement->execute([
            'project_id' => (int) $data['project_id'],
            'proposal_id' => $data['proposal_id'] ?? null,
            'uploader_id' => (int) $data['uploader_id'],
            'original_name' => $data['original_name'],
            'stored_name' => $data['stored_name'],
            'mime_type' => $data['mime_type'],
            'file_size' => (int) $data['file_size'],
            'visibility' => $data['visibility'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Returns recent files visible to a project participant.
     * RU: Возвращает последние файлы, видимые участнику проекта.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function recentForUser(int $userId, int $limit = 12): array
    {
        $statement = $this->pdo->prepare(
            'SELECT f.*, p.title AS project_title, uploader.name AS uploader_name
             FROM project_files f
             INNER JOIN projects p ON p.id = f.project_id
             INNER JOIN users uploader ON uploader.id = f.uploader_id
             LEFT JOIN proposals pr ON pr.project_id = p.id AND pr.freelancer_id = :proposal_user_id
             WHERE p.client_id = :client_user_id
                OR p.assigned_freelancer_id = :assigned_user_id
                OR f.uploader_id = :uploader_user_id
                OR pr.id IS NOT NULL
             ORDER BY f.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('proposal_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('client_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('assigned_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('uploader_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Finds one file with project access fields.
     * RU: Ищет один файл с полями доступа проекта.
     *
     * @param int $fileId File id / ID файла
     * @return array<string, mixed>|null
     */
    public function find(int $fileId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT f.*, p.client_id, p.assigned_freelancer_id
             FROM project_files f
             INNER JOIN projects p ON p.id = f.project_id
             WHERE f.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $fileId]);
        $file = $statement->fetch();

        return $file ?: null;
    }
}
