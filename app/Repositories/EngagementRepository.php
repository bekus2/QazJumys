<?php
/**
 * Project: QazJumys
 * File: EngagementRepository.php
 * Author: Beck Sarbassov
 * Version: 1.3.0
 * Release Date: 2026-06-21
 * Last Updated: 2026-06-21
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Stores marketplace engagement features such as saved projects, saved searches, milestones, reviews, portfolio items, and verification requests.
 * RU: Хранит функции вовлечения маркетплейса: сохраненные проекты, поиски, milestones, reviews, портфолио и заявки на верификацию.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;
use RuntimeException;

final class EngagementRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Saves or unsaves a project for the current user.
     * RU: Сохраняет или убирает проект из избранного текущего пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @param int $projectId Project id / ID проекта
     * @return bool True when saved, false when removed / true если сохранено, false если удалено
     */
    public function toggleSavedProject(int $userId, int $projectId): bool
    {
        $exists = $this->pdo->prepare('SELECT project_id FROM saved_projects WHERE user_id = :user_id AND project_id = :project_id LIMIT 1');
        $exists->execute(['user_id' => $userId, 'project_id' => $projectId]);

        if ($exists->fetch()) {
            $delete = $this->pdo->prepare('DELETE FROM saved_projects WHERE user_id = :user_id AND project_id = :project_id');
            $delete->execute(['user_id' => $userId, 'project_id' => $projectId]);
            return false;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO saved_projects (user_id, project_id, created_at)
             VALUES (:user_id, :project_id, NOW())'
        );
        $insert->execute(['user_id' => $userId, 'project_id' => $projectId]);

        return true;
    }

    /**
     * EN: Returns project ids saved by the user.
     * RU: Возвращает ID проектов, сохраненных пользователем.
     *
     * @param int $userId User id / ID пользователя
     * @return array<int, int>
     */
    public function savedProjectIds(int $userId): array
    {
        $statement = $this->pdo->prepare('SELECT project_id FROM saved_projects WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);

        return array_map('intval', array_column($statement->fetchAll(), 'project_id'));
    }

    /**
     * EN: Lists saved projects with marketplace context.
     * RU: Возвращает сохраненные проекты с контекстом маркетплейса.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function savedProjects(int $userId, int $limit = 8): array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name, owner.name AS client_name, sp.created_at AS saved_at,
                    (SELECT COUNT(*) FROM proposals pr WHERE pr.project_id = p.id) AS proposals_count
             FROM saved_projects sp
             INNER JOIN projects p ON p.id = sp.project_id
             INNER JOIN categories c ON c.id = p.category_id
             INNER JOIN users owner ON owner.id = p.client_id
             WHERE sp.user_id = :user_id
             ORDER BY sp.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Saves the current project search query for reuse.
     * RU: Сохраняет текущий поисковый запрос по проектам для повторного использования.
     *
     * @param int $userId User id / ID пользователя
     * @param string $label Search label / Название поиска
     * @param string $queryString Query string / Строка запроса
     * @return int
     */
    public function createSavedSearch(int $userId, string $label, string $queryString): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO saved_searches (user_id, label, query_string, created_at)
             VALUES (:user_id, :label, :query_string, NOW())'
        );
        $statement->execute([
            'user_id' => $userId,
            'label' => $label,
            'query_string' => $queryString,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Lists saved searches for the user.
     * RU: Возвращает сохраненные поиски пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function savedSearches(int $userId, int $limit = 8): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM saved_searches
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Creates a milestone on a project owned by the user.
     * RU: Создает milestone в проекте, принадлежащем пользователю.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $ownerId Owner user id / ID автора проекта
     * @param string $title Milestone title / Название milestone
     * @param string|null $dueDate Due date / Срок
     * @return int
     */
    public function createMilestone(int $projectId, int $ownerId, string $title, ?string $dueDate): int
    {
        $this->assertProjectOwner($projectId, $ownerId);
        $statement = $this->pdo->prepare(
            'INSERT INTO project_milestones (project_id, owner_id, title, due_date, status, created_at)
             VALUES (:project_id, :owner_id, :title, :due_date, "planned", NOW())'
        );
        $statement->execute([
            'project_id' => $projectId,
            'owner_id' => $ownerId,
            'title' => $title,
            'due_date' => $dueDate ?: null,
        ]);
        $this->touchProjectActivity($projectId);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Marks a milestone as done by project owner or assigned performer.
     * RU: Отмечает milestone выполненным автором проекта или назначенным исполнителем.
     *
     * @param int $milestoneId Milestone id / ID milestone
     * @param int $userId User id / ID пользователя
     * @return void
     */
    public function completeMilestone(int $milestoneId, int $userId): void
    {
        $milestone = $this->findMilestone($milestoneId);

        if (!$milestone || !$this->canTouchProject((int) $milestone['project_id'], $userId)) {
            throw new RuntimeException('Milestone табылмады немесе рұқсат жоқ.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE project_milestones
             SET status = "done", completed_at = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $milestoneId]);
        $this->touchProjectActivity((int) $milestone['project_id']);
    }

    /**
     * EN: Returns milestones for all projects visible to a user dashboard.
     * RU: Возвращает milestones для проектов, видимых в кабинете пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function milestonesForUser(int $userId, int $limit = 12): array
    {
        $statement = $this->pdo->prepare(
            'SELECT m.*, p.title AS project_title
             FROM project_milestones m
             INNER JOIN projects p ON p.id = m.project_id
             WHERE p.client_id = :client_user_id OR p.assigned_freelancer_id = :assigned_user_id
             ORDER BY m.status = "planned" DESC, m.due_date IS NULL ASC, m.due_date ASC, m.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('client_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('assigned_user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Creates a review after a completed project and recalculates the recipient rating.
     * RU: Создает отзыв после завершенного проекта и пересчитывает рейтинг получателя.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $reviewerId Reviewer id / ID автора отзыва
     * @param int $revieweeId Reviewee id / ID получателя
     * @param int $rating Rating 1-5 / Рейтинг 1-5
     * @param string $comment Review text / Текст отзыва
     * @return int
     */
    public function createReview(int $projectId, int $reviewerId, int $revieweeId, int $rating, string $comment): int
    {
        $project = $this->findCompletedProject($projectId);

        if (!$project || !$this->isProjectPair($project, $reviewerId, $revieweeId)) {
            throw new RuntimeException('Бұл жоба бойынша review қалдыруға рұқсат жоқ.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO reviews (project_id, reviewer_id, reviewee_id, rating, comment, created_at)
             VALUES (:project_id, :reviewer_id, :reviewee_id, :rating, :comment, NOW())'
        );
        $statement->execute([
            'project_id' => $projectId,
            'reviewer_id' => $reviewerId,
            'reviewee_id' => $revieweeId,
            'rating' => max(1, min(5, $rating)),
            'comment' => $comment,
        ]);
        $this->recalculateRating($revieweeId);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Lists completed projects where the user can still leave reviews.
     * RU: Возвращает завершенные проекты, где пользователь еще может оставить отзыв.
     *
     * @param int $userId User id / ID пользователя
     * @return array<int, array<string, mixed>>
     */
    public function pendingReviews(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.id AS project_id, p.title AS project_title, p.client_id, p.assigned_freelancer_id,
                    client.name AS client_name, assigned.name AS assigned_name,
                    CASE WHEN p.client_id = :select_client_user_id THEN p.assigned_freelancer_id ELSE p.client_id END AS reviewee_id,
                    CASE WHEN p.client_id = :select_name_user_id THEN assigned.name ELSE client.name END AS reviewee_name
             FROM projects p
             INNER JOIN users client ON client.id = p.client_id
             INNER JOIN users assigned ON assigned.id = p.assigned_freelancer_id
             WHERE p.status = "completed"
               AND (p.client_id = :client_user_id OR p.assigned_freelancer_id = :assigned_user_id)
               AND NOT EXISTS (
                    SELECT 1 FROM reviews r
                    WHERE r.project_id = p.id AND r.reviewer_id = :reviewer_user_id
               )
             ORDER BY p.completed_at DESC
             LIMIT 8'
        );
        $statement->execute([
            'select_client_user_id' => $userId,
            'select_name_user_id' => $userId,
            'client_user_id' => $userId,
            'assigned_user_id' => $userId,
            'reviewer_user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    /**
     * EN: Lists reviews received by a user.
     * RU: Возвращает отзывы, полученные пользователем.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function reviewsForUser(int $userId, int $limit = 6): array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.*, p.title AS project_title, reviewer.name AS reviewer_name
             FROM reviews r
             INNER JOIN projects p ON p.id = r.project_id
             INNER JOIN users reviewer ON reviewer.id = r.reviewer_id
             WHERE r.reviewee_id = :user_id
             ORDER BY r.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Adds a portfolio item to a member profile.
     * RU: Добавляет элемент портфолио в профиль участника.
     *
     * @param int $userId User id / ID пользователя
     * @param array<string, mixed> $data Portfolio data / Данные портфолио
     * @return int
     */
    public function createPortfolioItem(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO portfolio_items (user_id, title, description, url, skills, created_at)
             VALUES (:user_id, :title, :description, :url, :skills, NOW())'
        );
        $statement->execute([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'],
            'url' => $data['url'] ?: null,
            'skills' => $data['skills'] ?: null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Deletes a portfolio item owned by the user.
     * RU: Удаляет элемент портфолио, принадлежащий пользователю.
     *
     * @param int $itemId Portfolio item id / ID элемента
     * @param int $userId User id / ID пользователя
     * @return void
     */
    public function deletePortfolioItem(int $itemId, int $userId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM portfolio_items WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $itemId, 'user_id' => $userId]);
    }

    /**
     * EN: Lists portfolio items for a user.
     * RU: Возвращает элементы портфолио пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @return array<int, array<string, mixed>>
     */
    public function portfolioItems(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM portfolio_items
             WHERE user_id = :user_id
             ORDER BY created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    /**
     * EN: Creates a verification request unless one is already pending.
     * RU: Создает заявку на верификацию, если нет активной pending-заявки.
     *
     * @param int $userId User id / ID пользователя
     * @param string $note User note / Заметка пользователя
     * @return int
     */
    public function requestVerification(int $userId, string $note): int
    {
        $pending = $this->pdo->prepare('SELECT id FROM verification_requests WHERE user_id = :user_id AND status = "pending" LIMIT 1');
        $pending->execute(['user_id' => $userId]);

        if ($pending->fetch()) {
            throw new RuntimeException('Верификация бойынша pending заявка бар.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO verification_requests (user_id, status, note, created_at, updated_at)
             VALUES (:user_id, "pending", :note, NOW(), NOW())'
        );
        $statement->execute(['user_id' => $userId, 'note' => $note]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Returns the latest verification request for a user.
     * RU: Возвращает последнюю заявку на верификацию пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @return array<string, mixed>|null
     */
    public function latestVerificationForUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM verification_requests
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $request = $statement->fetch();

        return $request ?: null;
    }

    /**
     * EN: Lists verification requests for owner moderation.
     * RU: Возвращает заявки на верификацию для owner-модерации.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function verificationRequests(int $limit = 30): array
    {
        $statement = $this->pdo->prepare(
            'SELECT vr.*, u.name, u.email, u.city, u.headline
             FROM verification_requests vr
             INNER JOIN users u ON u.id = vr.user_id
             ORDER BY vr.status = "pending" DESC, vr.updated_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Approves or rejects a verification request and updates the user flag.
     * RU: Одобряет или отклоняет заявку на верификацию и обновляет флаг пользователя.
     *
     * @param int $requestId Request id / ID заявки
     * @param string $status approved|rejected / approved|rejected
     * @param string $ownerNote Owner note / Заметка владельца
     * @return array<string, mixed>
     */
    public function reviewVerification(int $requestId, string $status, string $ownerNote): array
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            throw new RuntimeException('Верификация статусы дұрыс емес.');
        }

        $this->pdo->beginTransaction();

        try {
            $request = $this->findVerificationForUpdate($requestId);

            if (!$request) {
                throw new RuntimeException('Верификация заявкасы табылмады.');
            }

            $statement = $this->pdo->prepare(
                'UPDATE verification_requests
                 SET status = :status, owner_note = :owner_note, updated_at = NOW(), reviewed_at = NOW()
                 WHERE id = :id'
            );
            $statement->execute([
                'id' => $requestId,
                'status' => $status,
                'owner_note' => $ownerNote,
            ]);

            $userUpdate = $this->pdo->prepare('UPDATE users SET is_verified = :is_verified, updated_at = NOW() WHERE id = :user_id');
            $userUpdate->execute([
                'user_id' => (int) $request['user_id'],
                'is_verified' => $status === 'approved' ? 1 : 0,
            ]);

            $this->pdo->commit();

            return array_merge($request, ['status' => $status]);
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * EN: Counts engagement records for dashboard cards.
     * RU: Считает записи вовлечения для карточек кабинета.
     *
     * @param int $userId User id / ID пользователя
     * @return array<string, int>
     */
    public function engagementStats(int $userId): array
    {
        return [
            'saved_projects' => $this->count('SELECT COUNT(*) FROM saved_projects WHERE user_id = :id', $userId),
            'saved_searches' => $this->count('SELECT COUNT(*) FROM saved_searches WHERE user_id = :id', $userId),
            'portfolio_items' => $this->count('SELECT COUNT(*) FROM portfolio_items WHERE user_id = :id', $userId),
            'pending_reviews' => count($this->pendingReviews($userId)),
        ];
    }

    private function assertProjectOwner(int $projectId, int $ownerId): void
    {
        $statement = $this->pdo->prepare('SELECT id FROM projects WHERE id = :project_id AND client_id = :owner_id LIMIT 1');
        $statement->execute(['project_id' => $projectId, 'owner_id' => $ownerId]);

        if (!$statement->fetch()) {
            throw new RuntimeException('Бұл жоба сізге тиесілі емес.');
        }
    }

    private function canTouchProject(int $projectId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT id
             FROM projects
             WHERE id = :project_id
               AND (client_id = :client_user_id OR assigned_freelancer_id = :assigned_user_id)
             LIMIT 1'
        );
        $statement->execute([
            'project_id' => $projectId,
            'client_user_id' => $userId,
            'assigned_user_id' => $userId,
        ]);

        return (bool) $statement->fetch();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findMilestone(int $milestoneId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM project_milestones WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $milestoneId]);
        $milestone = $statement->fetch();

        return $milestone ?: null;
    }

    private function touchProjectActivity(int $projectId): void
    {
        $statement = $this->pdo->prepare('UPDATE projects SET last_activity_at = NOW(), updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $projectId]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findCompletedProject(int $projectId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM projects WHERE id = :id AND status = "completed" LIMIT 1');
        $statement->execute(['id' => $projectId]);
        $project = $statement->fetch();

        return $project ?: null;
    }

    /**
     * @param array<string, mixed> $project Completed project / Завершенный проект
     */
    private function isProjectPair(array $project, int $reviewerId, int $revieweeId): bool
    {
        $clientId = (int) $project['client_id'];
        $freelancerId = (int) ($project['assigned_freelancer_id'] ?? 0);

        return ($reviewerId === $clientId && $revieweeId === $freelancerId)
            || ($reviewerId === $freelancerId && $revieweeId === $clientId);
    }

    private function recalculateRating(int $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET rating = COALESCE((SELECT ROUND(AVG(rating), 2) FROM reviews WHERE reviewee_id = :rating_user_id), 0),
                 reviews_count = (SELECT COUNT(*) FROM reviews WHERE reviewee_id = :count_user_id),
                 updated_at = NOW()
             WHERE id = :target_user_id'
        );
        $statement->execute([
            'rating_user_id' => $userId,
            'count_user_id' => $userId,
            'target_user_id' => $userId,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findVerificationForUpdate(int $requestId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM verification_requests WHERE id = :id FOR UPDATE');
        $statement->execute(['id' => $requestId]);
        $request = $statement->fetch();

        return $request ?: null;
    }

    private function count(string $sql, int $userId): int
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $userId]);

        return (int) $statement->fetchColumn();
    }
}
