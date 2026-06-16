<?php
/**
 * Project: QazJumys
 * File: OwnerRepository.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Provides owner dashboard statistics, moderation lists, and account management actions.
 * RU: Предоставляет статистику owner-панели, списки модерации и действия управления аккаунтами.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

final class OwnerRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Returns operational counters for the owner dashboard.
     * RU: Возвращает операционные счетчики для панели владельца.
     *
     * @return array<string, int|float>
     */
    public function stats(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                (SELECT COUNT(*) FROM users WHERE role <> "owner") AS members_total,
                (SELECT COUNT(*) FROM users WHERE status = "blocked") AS blocked_users,
                (SELECT COUNT(*) FROM projects) AS projects_total,
                (SELECT COUNT(*) FROM projects WHERE status = "open") AS projects_open,
                (SELECT COUNT(*) FROM projects WHERE status = "in_progress") AS projects_active,
                (SELECT COUNT(*) FROM projects WHERE status = "submitted") AS projects_submitted,
                (SELECT COUNT(*) FROM projects WHERE status = "completed") AS projects_completed,
                (SELECT COUNT(*) FROM proposals) AS proposals_total,
                (SELECT COUNT(*) FROM messages) AS messages_total,
                (SELECT COUNT(*) FROM project_files) AS files_total,
                (SELECT COUNT(*) FROM complaints WHERE status IN ("open", "reviewing")) AS complaints_open,
                (SELECT COALESCE(SUM(budget_max), 0) FROM projects WHERE status = "completed") AS completed_budget
             '
        );
        $stats = $statement->fetch() ?: [];

        return [
            'members_total' => (int) ($stats['members_total'] ?? 0),
            'blocked_users' => (int) ($stats['blocked_users'] ?? 0),
            'projects_total' => (int) ($stats['projects_total'] ?? 0),
            'projects_open' => (int) ($stats['projects_open'] ?? 0),
            'projects_active' => (int) ($stats['projects_active'] ?? 0),
            'projects_submitted' => (int) ($stats['projects_submitted'] ?? 0),
            'projects_completed' => (int) ($stats['projects_completed'] ?? 0),
            'proposals_total' => (int) ($stats['proposals_total'] ?? 0),
            'messages_total' => (int) ($stats['messages_total'] ?? 0),
            'files_total' => (int) ($stats['files_total'] ?? 0),
            'complaints_open' => (int) ($stats['complaints_open'] ?? 0),
            'completed_budget' => (float) ($stats['completed_budget'] ?? 0),
        ];
    }

    /**
     * EN: Lists member accounts for owner moderation.
     * RU: Возвращает аккаунты участников для owner-модерации.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function users(int $limit = 60): array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.*,
                    (SELECT COUNT(*) FROM projects p WHERE p.client_id = u.id) AS projects_count,
                    (SELECT COUNT(*) FROM proposals pr WHERE pr.freelancer_id = u.id) AS proposals_count
             FROM users u
             ORDER BY u.role = "owner" DESC, u.status = "blocked" DESC, u.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Lists recent projects with owner-relevant context.
     * RU: Возвращает последние проекты с контекстом для владельца.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function projects(int $limit = 40): array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name, owner.name AS client_name, assigned.name AS assigned_name,
                    (SELECT COUNT(*) FROM proposals pr WHERE pr.project_id = p.id) AS proposals_count
             FROM projects p
             INNER JOIN categories c ON c.id = p.category_id
             INNER JOIN users owner ON owner.id = p.client_id
             LEFT JOIN users assigned ON assigned.id = p.assigned_freelancer_id
             ORDER BY p.updated_at DESC, p.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Lists complaints for moderation.
     * RU: Возвращает жалобы для модерации.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function complaints(int $limit = 40): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.*, reporter.name AS reporter_name, reported.name AS reported_name, p.title AS project_title
             FROM complaints c
             INNER JOIN users reporter ON reporter.id = c.reporter_id
             LEFT JOIN users reported ON reported.id = c.reported_user_id
             LEFT JOIN projects p ON p.id = c.project_id
             ORDER BY c.status IN ("open", "reviewing") DESC, c.updated_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Lists recent email log rows for delivery troubleshooting.
     * RU: Возвращает последние записи email-журнала для диагностики доставки.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function emailLogs(int $limit = 20): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM email_logs
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Lists recent audit actions.
     * RU: Возвращает последние audit-действия.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function auditLogs(int $limit = 20): array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.*, u.name AS owner_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.owner_id
             ORDER BY a.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Blocks a user account.
     * RU: Блокирует аккаунт пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @param string $reason Moderation reason / Причина модерации
     * @return void
     */
    public function blockUser(int $userId, string $reason): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET status = "blocked", blocked_reason = :reason, blocked_at = NOW(), updated_at = NOW()
             WHERE id = :id AND role <> "owner"'
        );
        $statement->execute(['id' => $userId, 'reason' => $reason]);
    }

    /**
     * EN: Restores a blocked user account.
     * RU: Разблокирует аккаунт пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @return void
     */
    public function unblockUser(int $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET status = "active", blocked_reason = NULL, blocked_at = NULL, updated_at = NOW()
             WHERE id = :id AND role <> "owner"'
        );
        $statement->execute(['id' => $userId]);
    }

    /**
     * EN: Sets a temporary password hash and forces a password reset flag.
     * RU: Устанавливает временный хеш пароля и включает флаг смены пароля.
     *
     * @param int $userId User id / ID пользователя
     * @param string $temporaryPassword Temporary password / Временный пароль
     * @return void
     */
    public function resetPassword(int $userId, string $temporaryPassword): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET password_hash = :hash, password_reset_required = 1, updated_at = NOW()
             WHERE id = :id AND role <> "owner"'
        );
        $statement->execute([
            'id' => $userId,
            'hash' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * EN: Updates a complaint status and owner note.
     * RU: Обновляет статус жалобы и заметку владельца.
     *
     * @param int $complaintId Complaint id / ID жалобы
     * @param string $status New status / Новый статус
     * @param string $note Owner note / Заметка владельца
     * @return void
     */
    public function updateComplaint(int $complaintId, string $status, string $note): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE complaints
             SET status = :status, owner_note = :note, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $complaintId,
            'status' => $status,
            'note' => $note,
        ]);
    }

    /**
     * EN: Changes a project status from owner panel for emergency moderation.
     * RU: Меняет статус проекта из owner-панели для экстренной модерации.
     *
     * @param int $projectId Project id / ID проекта
     * @param string $status New status / Новый статус
     * @return void
     */
    public function updateProjectStatus(int $projectId, string $status): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE projects
             SET status = :status,
                 cancelled_at = CASE WHEN :status_cancelled = "cancelled" THEN NOW() ELSE cancelled_at END,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $projectId,
            'status' => $status,
            'status_cancelled' => $status,
        ]);
    }

    /**
     * EN: Records owner actions for traceability.
     * RU: Записывает действия владельца для отслеживаемости.
     *
     * @param int|null $ownerId Owner id / ID владельца
     * @param string $action Action key / Ключ действия
     * @param string $entityType Entity type / Тип сущности
     * @param int|null $entityId Entity id / ID сущности
     * @param string $details Details / Детали
     * @return void
     */
    public function audit(?int $ownerId, string $action, string $entityType, ?int $entityId, string $details): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO audit_logs (owner_id, action, entity_type, entity_id, details, created_at)
             VALUES (:owner_id, :action, :entity_type, :entity_id, :details, NOW())'
        );
        $statement->execute([
            'owner_id' => $ownerId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
        ]);
    }
}
