<?php
/**
 * Project: QazJumys
 * File: FileRepository.php
 * Author: Beck Sarbassov
 * Version: 1.4.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-28
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
             LEFT JOIN proposals file_pr ON file_pr.id = f.proposal_id
             LEFT JOIN proposals user_pr ON user_pr.project_id = p.id
                AND user_pr.freelancer_id = :proposal_user_id
                AND user_pr.status IN ("sent", "shortlisted", "accepted", "completed")
             WHERE p.client_id = :client_user_id
                OR p.assigned_freelancer_id = :assigned_user_id
                OR f.uploader_id = :uploader_user_id
                OR (f.visibility = "brief" AND user_pr.id IS NOT NULL)
                OR (f.visibility = "proposal" AND file_pr.freelancer_id = :proposal_file_user_id AND file_pr.status IN ("sent", "shortlisted", "accepted", "completed"))
             ORDER BY f.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('proposal_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('client_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('assigned_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('uploader_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('proposal_file_user_id', $userId, PDO::PARAM_INT);
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
            'SELECT f.*, p.client_id, p.assigned_freelancer_id,
                    pr.freelancer_id AS proposal_freelancer_id, pr.status AS proposal_status
             FROM project_files f
             INNER JOIN projects p ON p.id = f.project_id
             LEFT JOIN proposals pr ON pr.id = f.proposal_id
             WHERE f.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $fileId]);
        $file = $statement->fetch();

        return $file ?: null;
    }

    /**
     * EN: Checks download access using separated brief/proposal/delivery visibility rules.
     * RU: Проверяет скачивание по отдельным правилам видимости brief/proposal/delivery.
     *
     * @param int $fileId File id / ID файла
     * @param int $userId User id / ID пользователя
     * @param bool $isOwner Platform owner flag / Флаг владельца платформы
     * @return bool
     */
    public function canAccess(int $fileId, int $userId, bool $isOwner = false): bool
    {
        if ($isOwner) {
            return true;
        }

        $statement = $this->pdo->prepare(
            'SELECT f.id
             FROM project_files f
             INNER JOIN projects p ON p.id = f.project_id
             LEFT JOIN proposals file_pr ON file_pr.id = f.proposal_id
             LEFT JOIN proposals user_pr ON user_pr.project_id = p.id
                AND user_pr.freelancer_id = :active_proposal_user_id
                AND user_pr.status IN ("sent", "shortlisted", "accepted", "completed")
             WHERE f.id = :file_id
               AND (
                    p.client_id = :client_user_id
                    OR f.uploader_id = :uploader_user_id
                    OR (f.visibility = "brief" AND (p.assigned_freelancer_id = :brief_assigned_user_id OR user_pr.id IS NOT NULL))
                    OR (f.visibility = "proposal" AND file_pr.freelancer_id = :proposal_file_user_id AND file_pr.status IN ("sent", "shortlisted", "accepted", "completed"))
                    OR (f.visibility = "delivery" AND p.assigned_freelancer_id = :delivery_assigned_user_id)
               )
             LIMIT 1'
        );
        $statement->execute([
            'file_id' => $fileId,
            'active_proposal_user_id' => $userId,
            'client_user_id' => $userId,
            'uploader_user_id' => $userId,
            'brief_assigned_user_id' => $userId,
            'proposal_file_user_id' => $userId,
            'delivery_assigned_user_id' => $userId,
        ]);

        return (bool) $statement->fetch();
    }

    /**
     * EN: Checks whether a user may upload a file with the requested project visibility.
     * RU: Проверяет право загрузки файла с указанной видимостью проекта.
     *
     * @param int $projectId Project id / ID проекта
     * @param int|null $proposalId Proposal id / ID отклика
     * @param int $userId Uploader id / ID загрузившего
     * @param string $visibility brief|proposal|delivery
     * @return bool
     */
    public function canUpload(int $projectId, ?int $proposalId, int $userId, string $visibility): bool
    {
        if ($projectId <= 0 || $userId <= 0 || !in_array($visibility, ['brief', 'proposal', 'delivery'], true)) {
            return false;
        }

        if ($visibility === 'brief') {
            $statement = $this->pdo->prepare(
                'SELECT id FROM projects WHERE id = :project_id AND client_id = :user_id LIMIT 1'
            );
            $statement->execute(['project_id' => $projectId, 'user_id' => $userId]);

            return (bool) $statement->fetch();
        }

        if ($visibility === 'proposal') {
            if ($proposalId === null) {
                return false;
            }

            $statement = $this->pdo->prepare(
                'SELECT id
                 FROM proposals
                 WHERE id = :proposal_id
                   AND project_id = :project_id
                   AND freelancer_id = :user_id
                   AND status IN ("sent", "shortlisted", "accepted")
                 LIMIT 1'
            );
            $statement->execute([
                'proposal_id' => $proposalId,
                'project_id' => $projectId,
                'user_id' => $userId,
            ]);

            return (bool) $statement->fetch();
        }

        $statement = $this->pdo->prepare(
            'SELECT p.id
             FROM projects p
             LEFT JOIN proposals pr ON pr.id = :proposal_id AND pr.project_id = p.id
             WHERE p.id = :project_id
               AND p.assigned_freelancer_id = :user_id
               AND p.status IN ("in_progress", "submitted")
               AND (:proposal_id_check IS NULL OR pr.freelancer_id = :proposal_user_id)
             LIMIT 1'
        );
        $statement->execute([
            'proposal_id' => $proposalId,
            'project_id' => $projectId,
            'user_id' => $userId,
            'proposal_id_check' => $proposalId,
            'proposal_user_id' => $userId,
        ]);

        return (bool) $statement->fetch();
    }
}
