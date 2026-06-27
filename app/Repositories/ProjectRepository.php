<?php
/**
 * Project: QazJumys
 * File: ProjectRepository.php
 * Author: Beck Sarbassov
 * Version: 1.4.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-28
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Stores projects, proposal bids, workflow transitions, dashboard counters, activity metrics, and marketplace discovery data.
 * RU: Хранит проекты, отклики, переходы workflow, показатели кабинета, метрики активности и данные поиска маркетплейса.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;
use RuntimeException;

final class ProjectRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Returns recently opened projects for homepage and dashboard previews.
     * RU: Возвращает свежие открытые проекты для главной страницы и кабинета.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function latestOpen(int $limit = 6): array
    {
        $statement = $this->pdo->prepare(
            $this->projectSelect() . '
             WHERE p.status = "open"
             ORDER BY p.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Returns curated highlighted projects for the public marketplace.
     * RU: Возвращает выделенные проекты для публичного маркетплейса.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function featuredOpen(int $limit = 3): array
    {
        $statement = $this->pdo->prepare(
            $this->projectSelect() . '
             WHERE p.status = "open" AND p.is_featured = 1
             ORDER BY p.is_urgent DESC, p.budget_max DESC, p.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Searches open projects by keyword, category, budget, type, experience level, status signals, and sort order.
     * RU: Ищет открытые проекты по слову, категории, бюджету, типу, уровню опыта, статусным признакам и сортировке.
     *
     * @param array<string, mixed> $filters Search filters / Фильтры поиска
     * @return array<int, array<string, mixed>>
     */
    public function searchOpen(array $filters = []): array
    {
        $where = ['p.status = "open"'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(p.title LIKE :query_title OR p.description LIKE :query_description OR p.skills LIKE :query_skills)';
            $params['query_title'] = '%' . $filters['q'] . '%';
            $params['query_description'] = '%' . $filters['q'] . '%';
            $params['query_skills'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['project_type']) && in_array($filters['project_type'], ['fixed', 'hourly'], true)) {
            $where[] = 'p.project_type = :project_type';
            $params['project_type'] = (string) $filters['project_type'];
        }

        if (!empty($filters['experience_level']) && in_array($filters['experience_level'], ['entry', 'intermediate', 'expert'], true)) {
            $where[] = 'p.experience_level = :experience_level';
            $params['experience_level'] = (string) $filters['experience_level'];
        }

        if (isset($filters['budget_min']) && $filters['budget_min'] !== null && $filters['budget_min'] !== '') {
            $where[] = 'p.budget_max >= :budget_min';
            $params['budget_min'] = (float) $filters['budget_min'];
        }

        if (isset($filters['budget_max']) && $filters['budget_max'] !== null && $filters['budget_max'] !== '') {
            $where[] = 'p.budget_min <= :budget_max';
            $params['budget_max'] = (float) $filters['budget_max'];
        }

        if (!empty($filters['is_remote'])) {
            $where[] = 'p.is_remote = 1';
        }

        if (!empty($filters['is_urgent'])) {
            $where[] = 'p.is_urgent = 1';
        }

        if (!empty($filters['verified_client'])) {
            $where[] = 'u.is_verified = 1';
        }

        $orderBy = match ((string) ($filters['sort'] ?? 'latest')) {
            'budget_high' => 'p.budget_max DESC, p.created_at DESC',
            'budget_low' => 'p.budget_min ASC, p.created_at DESC',
            'deadline_soon' => 'p.deadline_days ASC, p.created_at DESC',
            'proposals_low' => 'proposals_count ASC, p.created_at DESC',
            'views_high' => 'p.views_count DESC, p.created_at DESC',
            'activity' => 'p.last_activity_at DESC, p.created_at DESC',
            default => 'p.is_featured DESC, p.is_urgent DESC, p.created_at DESC',
        };

        $statement = $this->pdo->prepare(
            $this->projectSelect() . '
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY ' . $orderBy
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * EN: Creates a new marketplace project for any active member account.
     * RU: Создает новый проект маркетплейса для любого активного аккаунта участника.
     *
     * @param int $clientId Publisher user id / ID автора проекта
     * @param array<string, mixed> $data Project data / Данные проекта
     * @return int
     */
    public function create(int $clientId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO projects (
                client_id, category_id, title, description, project_type, experience_level, skills, location,
                is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, last_activity_at, created_at, updated_at
             )
             VALUES (
                :client_id, :category_id, :title, :description, :project_type, :experience_level, :skills, :location,
                :is_remote, 0, :is_urgent, :budget_min, :budget_max, :deadline_days, "open", NOW(), NOW(), NOW()
             )'
        );

        $statement->execute([
            'client_id' => $clientId,
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'project_type' => $data['project_type'],
            'experience_level' => $data['experience_level'],
            'skills' => $data['skills'],
            'location' => $data['location'],
            'is_remote' => !empty($data['is_remote']) ? 1 : 0,
            'is_urgent' => !empty($data['is_urgent']) ? 1 : 0,
            'budget_min' => $data['budget_min'],
            'budget_max' => $data['budget_max'],
            'deadline_days' => (int) $data['deadline_days'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Records lightweight project impressions after search/home rendering.
     * RU: Фиксирует легкие просмотры проектов после вывода поиска или главной страницы.
     *
     * @param array<int, mixed> $projectIds Project ids / ID проектов
     * @return void
     */
    public function recordImpressions(array $projectIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $projectIds))));

        if ($ids === []) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = $this->pdo->prepare(
            'UPDATE projects
             SET views_count = views_count + 1
             WHERE id IN (' . $placeholders . ')'
        );
        $statement->execute($ids);
    }

    /**
     * EN: Returns one project with owner, assignee, category, and proposal counters.
     * RU: Возвращает проект с автором, исполнителем, категорией и счетчиками откликов.
     *
     * @param int $projectId Project id / ID проекта
     * @return array<string, mixed>|null
     */
    public function findProject(int $projectId): ?array
    {
        $statement = $this->pdo->prepare($this->projectSelect() . ' WHERE p.id = :id LIMIT 1');
        $statement->execute(['id' => $projectId]);
        $project = $statement->fetch();

        return $project ?: null;
    }

    /**
     * EN: Returns projects published by the user.
     * RU: Возвращает проекты, опубликованные пользователем.
     *
     * @param int $clientId Publisher user id / ID автора
     * @return array<int, array<string, mixed>>
     */
    public function byClient(int $clientId): array
    {
        $statement = $this->pdo->prepare(
            $this->projectSelect() . '
             WHERE p.client_id = :client_id
             ORDER BY p.updated_at DESC'
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll();
    }

    /**
     * EN: Returns proposals submitted by the user with project context.
     * RU: Возвращает отклики пользователя вместе с контекстом проекта.
     *
     * @param int $freelancerId User id / ID пользователя
     * @return array<int, array<string, mixed>>
     */
    public function proposalsByFreelancer(int $freelancerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT pr.*, p.title AS project_title, p.status AS project_status, p.client_id,
                    p.project_type, p.experience_level, p.budget_min, p.budget_max,
                    p.assigned_freelancer_id, p.accepted_proposal_id, c.name AS category_name,
                    u.name AS client_name, u.email AS client_email
             FROM proposals pr
             INNER JOIN projects p ON p.id = pr.project_id
             INNER JOIN categories c ON c.id = p.category_id
             INNER JOIN users u ON u.id = p.client_id
             WHERE pr.freelancer_id = :freelancer_id
             ORDER BY pr.updated_at DESC, pr.created_at DESC'
        );
        $statement->execute(['freelancer_id' => $freelancerId]);

        return $statement->fetchAll();
    }

    /**
     * EN: Returns proposals received on projects published by the user.
     * RU: Возвращает отклики, полученные на проекты пользователя.
     *
     * @param int $clientId Publisher user id / ID автора проектов
     * @return array<int, array<string, mixed>>
     */
    public function proposalsForClient(int $clientId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT pr.*, p.title AS project_title, p.status AS project_status, p.client_id,
                    u.name AS freelancer_name, u.email AS freelancer_email, u.city AS freelancer_city,
                    u.headline AS freelancer_headline, u.rating AS freelancer_rating,
                    u.completed_projects AS freelancer_completed_projects
             FROM proposals pr
             INNER JOIN projects p ON p.id = pr.project_id
             INNER JOIN users u ON u.id = pr.freelancer_id
             WHERE p.client_id = :client_id
             ORDER BY pr.status = "accepted" DESC, pr.updated_at DESC, pr.created_at DESC'
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll();
    }

    /**
     * EN: Checks whether a project can still receive proposals.
     * RU: Проверяет, может ли проект принимать отклики.
     *
     * @param int $projectId Project id / ID проекта
     * @return bool
     */
    public function isOpen(int $projectId): bool
    {
        $statement = $this->pdo->prepare('SELECT id FROM projects WHERE id = :id AND status = "open" LIMIT 1');
        $statement->execute(['id' => $projectId]);

        return (bool) $statement->fetch();
    }

    /**
     * EN: Checks whether the user published the project.
     * RU: Проверяет, является ли пользователь автором проекта.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $userId User id / ID пользователя
     * @return bool
     */
    public function isProjectOwner(int $projectId, int $userId): bool
    {
        $statement = $this->pdo->prepare('SELECT id FROM projects WHERE id = :project_id AND client_id = :user_id LIMIT 1');
        $statement->execute(['project_id' => $projectId, 'user_id' => $userId]);

        return (bool) $statement->fetch();
    }

    /**
     * EN: Checks whether the user can access project communication and files.
     * RU: Проверяет доступ пользователя к коммуникациям и файлам проекта.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $userId User id / ID пользователя
     * @return bool
     */
    public function isParticipant(int $projectId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT p.id
             FROM projects p
             LEFT JOIN proposals pr ON pr.project_id = p.id
                AND pr.freelancer_id = :proposal_user_id
                AND pr.status IN ("sent", "shortlisted", "accepted", "completed")
             WHERE p.id = :project_id
               AND (p.client_id = :client_user_id OR p.assigned_freelancer_id = :assigned_user_id OR pr.id IS NOT NULL)
             LIMIT 1'
        );
        $statement->execute([
            'project_id' => $projectId,
            'proposal_user_id' => $userId,
            'client_user_id' => $userId,
            'assigned_user_id' => $userId,
        ]);

        return (bool) $statement->fetch();
    }

    /**
     * EN: Checks whether two users may exchange messages on a project/proposal pair.
     * RU: Проверяет, могут ли два пользователя обмениваться сообщениями по проекту/отклику.
     *
     * @param int $projectId Project id / ID проекта
     * @param int|null $proposalId Optional proposal id / ID отклика
     * @param int $senderId Sender user id / ID отправителя
     * @param int $receiverId Receiver user id / ID получателя
     * @return bool
     */
    public function canMessage(int $projectId, ?int $proposalId, int $senderId, int $receiverId): bool
    {
        if ($projectId <= 0 || $senderId <= 0 || $receiverId <= 0 || $senderId === $receiverId) {
            return false;
        }

        if ($proposalId !== null) {
            $statement = $this->pdo->prepare(
                'SELECT pr.id
                 FROM proposals pr
                 INNER JOIN projects p ON p.id = pr.project_id
                 WHERE pr.id = :proposal_id
                   AND pr.project_id = :project_id
                   AND pr.status IN ("sent", "shortlisted", "accepted", "completed")
                   AND (
                        (p.client_id = :sender_client_id AND pr.freelancer_id = :receiver_freelancer_id)
                        OR
                        (p.client_id = :receiver_client_id AND pr.freelancer_id = :sender_freelancer_id)
                   )
                 LIMIT 1'
            );
            $statement->execute([
                'proposal_id' => $proposalId,
                'project_id' => $projectId,
                'sender_client_id' => $senderId,
                'receiver_freelancer_id' => $receiverId,
                'receiver_client_id' => $receiverId,
                'sender_freelancer_id' => $senderId,
            ]);

            return (bool) $statement->fetch();
        }

        $statement = $this->pdo->prepare(
            'SELECT id
             FROM projects
             WHERE id = :project_id
               AND assigned_freelancer_id IS NOT NULL
               AND (
                    (client_id = :sender_client_id AND assigned_freelancer_id = :receiver_freelancer_id)
                    OR
                    (client_id = :receiver_client_id AND assigned_freelancer_id = :sender_freelancer_id)
               )
             LIMIT 1'
        );
        $statement->execute([
            'project_id' => $projectId,
            'sender_client_id' => $senderId,
            'receiver_freelancer_id' => $receiverId,
            'receiver_client_id' => $receiverId,
            'sender_freelancer_id' => $senderId,
        ]);

        return (bool) $statement->fetch();
    }

    /**
     * EN: Creates a proposal bid for an open project.
     * RU: Создает отклик на открытый проект.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $freelancerId User id / ID пользователя
     * @param array<string, mixed> $data Proposal data / Данные отклика
     * @return int
     */
    public function createProposal(int $projectId, int $freelancerId, array $data): int
    {
        $project = $this->findProject($projectId);

        if (!$project || $project['status'] !== 'open') {
            throw new RuntimeException('Бұл жобаға ұсыныс қабылданбайды.');
        }

        if ((int) $project['client_id'] === $freelancerId) {
            throw new RuntimeException('Өз жобаңызға отклик жіберуге болмайды.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_days, status, created_at, updated_at)
             VALUES (:project_id, :freelancer_id, :cover_letter, :bid_amount, :delivery_days, "sent", NOW(), NOW())'
        );

        $statement->execute([
            'project_id' => $projectId,
            'freelancer_id' => $freelancerId,
            'cover_letter' => $data['cover_letter'],
            'bid_amount' => $data['bid_amount'],
            'delivery_days' => (int) $data['delivery_days'],
        ]);
        $proposalId = (int) $this->pdo->lastInsertId();
        $this->touchProjectActivity($projectId);

        return $proposalId;
    }

    /**
     * EN: Prevents duplicate proposals for the same project.
     * RU: Предотвращает повторные отклики на один проект.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $freelancerId User id / ID пользователя
     * @return bool
     */
    public function hasProposal(int $projectId, int $freelancerId): bool
    {
        $statement = $this->pdo->prepare('SELECT id FROM proposals WHERE project_id = :project_id AND freelancer_id = :freelancer_id LIMIT 1');
        $statement->execute([
            'project_id' => $projectId,
            'freelancer_id' => $freelancerId,
        ]);

        return (bool) $statement->fetch();
    }

    /**
     * EN: Marks a proposal as shortlisted by the project owner.
     * RU: Отмечает отклик как shortlisted со стороны автора проекта.
     *
     * @param int $proposalId Proposal id / ID отклика
     * @param int $clientId Project owner id / ID автора проекта
     * @return array<string, mixed>
     */
    public function shortlistProposal(int $proposalId, int $clientId): array
    {
        $proposal = $this->findProposal($proposalId);

        if (!$proposal || (int) $proposal['client_id'] !== $clientId) {
            throw new RuntimeException('Отклик табылмады немесе сізге тиесілі емес.');
        }

        if (!in_array((string) $proposal['status'], ['sent', 'shortlisted'], true) || (string) $proposal['project_status'] !== 'open') {
            throw new RuntimeException('Бұл откликті shortlist ету мүмкін емес.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE proposals
             SET status = "shortlisted", updated_at = NOW()
             WHERE id = :proposal_id'
        );
        $statement->execute(['proposal_id' => $proposalId]);
        $this->touchProjectActivity((int) $proposal['project_id']);

        return $this->findProposal($proposalId) ?? [];
    }

    /**
     * EN: Withdraws an own proposal while the project has not accepted it.
     * RU: Отзывает собственный отклик, пока он не принят по проекту.
     *
     * @param int $proposalId Proposal id / ID отклика
     * @param int $freelancerId Proposal author id / ID автора отклика
     * @return array<string, mixed>
     */
    public function withdrawProposal(int $proposalId, int $freelancerId): array
    {
        $proposal = $this->findProposal($proposalId);

        if (!$proposal || (int) $proposal['freelancer_id'] !== $freelancerId) {
            throw new RuntimeException('Отклик табылмады немесе сізге тиесілі емес.');
        }

        if (!in_array((string) $proposal['status'], ['sent', 'shortlisted'], true)) {
            throw new RuntimeException('Бұл откликті қайтарып алу мүмкін емес.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE proposals
             SET status = "withdrawn", updated_at = NOW()
             WHERE id = :proposal_id'
        );
        $statement->execute(['proposal_id' => $proposalId]);
        $this->touchProjectActivity((int) $proposal['project_id']);

        return $this->findProposal($proposalId) ?? [];
    }

    /**
     * EN: Accepts one proposal and moves the project into active work.
     * RU: Принимает один отклик и переводит проект в активную работу.
     *
     * @param int $proposalId Proposal id / ID отклика
     * @param int $clientId Project owner id / ID автора проекта
     * @return array<string, mixed>
     */
    public function acceptProposal(int $proposalId, int $clientId): array
    {
        $this->pdo->beginTransaction();

        try {
            $proposal = $this->proposalWithProjectForUpdate($proposalId);

            if (!$proposal || (int) $proposal['client_id'] !== $clientId) {
                throw new RuntimeException('Отклик табылмады немесе сізге тиесілі емес.');
            }

            if (!in_array((string) $proposal['status'], ['sent', 'shortlisted'], true)) {
                throw new RuntimeException('Бұл отклик енді қабылдауға жарамайды.');
            }

            if ((string) $proposal['project_status'] !== 'open') {
                throw new RuntimeException('Бұл жоба бойынша отклик қабылдау мүмкін емес.');
            }

            if (!empty($proposal['accepted_proposal_id']) || !empty($proposal['assigned_freelancer_id'])) {
                throw new RuntimeException('Бұл жоба бойынша орындаушы бұрыннан қабылданған.');
            }

            $updateProposal = $this->pdo->prepare(
                'UPDATE proposals
                 SET status = "accepted", accepted_at = NOW(), updated_at = NOW()
                 WHERE id = :proposal_id'
            );
            $updateProposal->execute(['proposal_id' => $proposalId]);

            $declineOthers = $this->pdo->prepare(
                'UPDATE proposals
                 SET status = "declined", declined_at = NOW(), updated_at = NOW()
                 WHERE project_id = :project_id AND id <> :proposal_id AND status IN ("sent", "shortlisted")'
            );
            $declineOthers->execute([
                'project_id' => (int) $proposal['project_id'],
                'proposal_id' => $proposalId,
            ]);

            $updateProject = $this->pdo->prepare(
                'UPDATE projects
                 SET status = "in_progress",
                     accepted_proposal_id = :proposal_id,
                     assigned_freelancer_id = :freelancer_id,
                     started_at = COALESCE(started_at, NOW()),
                     last_activity_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :project_id AND status = "open" AND accepted_proposal_id IS NULL'
            );
            $updateProject->execute([
                'proposal_id' => $proposalId,
                'freelancer_id' => (int) $proposal['freelancer_id'],
                'project_id' => (int) $proposal['project_id'],
            ]);

            if ($updateProject->rowCount() < 1) {
                throw new RuntimeException('Жоба күйі өзгерген. Откликті қайта қабылдау мүмкін емес.');
            }

            $this->pdo->commit();

            return $this->findProposal($proposalId) ?? [];
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * EN: Declines a proposal without closing the project.
     * RU: Отклоняет отклик без закрытия проекта.
     *
     * @param int $proposalId Proposal id / ID отклика
     * @param int $clientId Project owner id / ID автора проекта
     * @return array<string, mixed>
     */
    public function declineProposal(int $proposalId, int $clientId): array
    {
        $proposal = $this->findProposal($proposalId);

        if (!$proposal || (int) $proposal['client_id'] !== $clientId) {
            throw new RuntimeException('Отклик табылмады немесе сізге тиесілі емес.');
        }

        if ((string) $proposal['status'] === 'accepted') {
            throw new RuntimeException('Қабылданған откликті жай ғана отклон етуге болмайды.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE proposals
             SET status = "declined", declined_at = NOW(), updated_at = NOW()
             WHERE id = :proposal_id'
        );
        $statement->execute(['proposal_id' => $proposalId]);
        $this->touchProjectActivity((int) $proposal['project_id']);

        return $this->findProposal($proposalId) ?? [];
    }

    /**
     * EN: Cancels a project by its owner before final completion.
     * RU: Отменяет проект автором до финального завершения.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $clientId Project owner id / ID автора проекта
     * @return array<string, mixed>
     */
    public function cancelProject(int $projectId, int $clientId): array
    {
        $this->pdo->beginTransaction();

        try {
            $project = $this->findProjectForUpdate($projectId);

            if (!$project || (int) $project['client_id'] !== $clientId) {
                throw new RuntimeException('Жоба табылмады немесе сізге тиесілі емес.');
            }

            if (in_array((string) $project['status'], ['completed', 'cancelled'], true)) {
                throw new RuntimeException('Бұл жобаны енді тоқтатуға болмайды.');
            }

            $projectUpdate = $this->pdo->prepare(
                'UPDATE projects
                 SET status = "cancelled", cancelled_at = NOW(), last_activity_at = NOW(), updated_at = NOW()
                 WHERE id = :project_id'
            );
            $projectUpdate->execute(['project_id' => $projectId]);

            $proposalUpdate = $this->pdo->prepare(
                'UPDATE proposals
                 SET status = "declined", declined_at = COALESCE(declined_at, NOW()), updated_at = NOW()
                 WHERE project_id = :project_id AND status IN ("sent", "shortlisted")'
            );
            $proposalUpdate->execute(['project_id' => $projectId]);

            $this->pdo->commit();

            return $this->findProject($projectId) ?? [];
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * EN: Marks active work as delivered by the assigned freelancer.
     * RU: Отмечает активную работу как сданную назначенным исполнителем.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $freelancerId Assigned user id / ID назначенного исполнителя
     * @return array<string, mixed>
     */
    public function submitDelivery(int $projectId, int $freelancerId): array
    {
        $statement = $this->pdo->prepare(
            'UPDATE projects
             SET status = "submitted", submitted_at = NOW(), last_activity_at = NOW(), updated_at = NOW()
             WHERE id = :project_id AND assigned_freelancer_id = :freelancer_id AND status = "in_progress"'
        );
        $statement->execute([
            'project_id' => $projectId,
            'freelancer_id' => $freelancerId,
        ]);

        if ($statement->rowCount() < 1) {
            throw new RuntimeException('Жұмысты тапсыру үшін сіз осы жобаның қабылданған орындаушысы болуыңыз керек.');
        }

        return $this->findProject($projectId) ?? [];
    }

    /**
     * EN: Completes delivered work and updates freelancer counters.
     * RU: Завершает сданную работу и обновляет счетчики исполнителя.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $clientId Project owner id / ID автора проекта
     * @return array<string, mixed>
     */
    public function completeProject(int $projectId, int $clientId): array
    {
        $this->pdo->beginTransaction();

        try {
            $project = $this->findProjectForUpdate($projectId);

            if (!$project || (int) $project['client_id'] !== $clientId) {
                throw new RuntimeException('Жоба табылмады немесе сізге тиесілі емес.');
            }

            if (!in_array((string) $project['status'], ['in_progress', 'submitted'], true)) {
                throw new RuntimeException('Бұл жоба аяқтауға дайын емес.');
            }

            $projectUpdate = $this->pdo->prepare(
                'UPDATE projects
                 SET status = "completed", completed_at = NOW(), last_activity_at = NOW(), updated_at = NOW()
                 WHERE id = :project_id'
            );
            $projectUpdate->execute(['project_id' => $projectId]);

            if (!empty($project['accepted_proposal_id'])) {
                $proposalUpdate = $this->pdo->prepare(
                    'UPDATE proposals
                     SET status = "completed", completed_at = NOW(), updated_at = NOW()
                     WHERE id = :proposal_id'
                );
                $proposalUpdate->execute(['proposal_id' => (int) $project['accepted_proposal_id']]);
            }

            if (!empty($project['assigned_freelancer_id'])) {
                $userUpdate = $this->pdo->prepare(
                    'UPDATE users
                     SET completed_projects = completed_projects + 1,
                         reviews_count = reviews_count + 1,
                         rating = CASE WHEN rating = 0 THEN 5.00 ELSE LEAST(5.00, rating + 0.02) END,
                         updated_at = NOW()
                     WHERE id = :freelancer_id'
                );
                $userUpdate->execute(['freelancer_id' => (int) $project['assigned_freelancer_id']]);
            }

            $this->pdo->commit();

            return $this->findProject($projectId) ?? [];
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * EN: Returns a proposal with related project and user contact data.
     * RU: Возвращает отклик с проектом и контактными данными пользователей.
     *
     * @param int $proposalId Proposal id / ID отклика
     * @return array<string, mixed>|null
     */
    public function findProposal(int $proposalId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT pr.*, p.title AS project_title, p.status AS project_status, p.client_id,
                    p.assigned_freelancer_id, owner.name AS client_name, owner.email AS client_email,
                    freelancer.name AS freelancer_name, freelancer.email AS freelancer_email
             FROM proposals pr
             INNER JOIN projects p ON p.id = pr.project_id
             INNER JOIN users owner ON owner.id = p.client_id
             INNER JOIN users freelancer ON freelancer.id = pr.freelancer_id
             WHERE pr.id = :proposal_id
             LIMIT 1'
        );
        $statement->execute(['proposal_id' => $proposalId]);
        $proposal = $statement->fetch();

        return $proposal ?: null;
    }

    /**
     * EN: Calculates unified dashboard counters.
     * RU: Считает показатели единого кабинета.
     *
     * @param int $userId User id / ID пользователя
     * @return array<string, int>
     */
    public function dashboardStats(int $userId): array
    {
        return [
            'projects' => $this->count('SELECT COUNT(*) FROM projects WHERE client_id = :id', $userId),
            'proposals' => $this->count('SELECT COUNT(*) FROM proposals WHERE freelancer_id = :id', $userId),
            'received_proposals' => $this->count(
                'SELECT COUNT(*) FROM proposals pr INNER JOIN projects p ON p.id = pr.project_id WHERE p.client_id = :id',
                $userId
            ),
            'active_work' => $this->count(
                'SELECT COUNT(*) FROM projects WHERE assigned_freelancer_id = :id AND status IN ("in_progress", "submitted")',
                $userId
            ),
            'open_market' => $this->countOpenProjects(),
        ];
    }

    /**
     * EN: Returns high-level counters for public marketplace sections.
     * RU: Возвращает общие показатели для публичных блоков маркетплейса.
     *
     * @return array<string, int>
     */
    public function marketplaceStats(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                (SELECT COUNT(*) FROM projects WHERE status = "open") AS open_projects,
                (SELECT COUNT(*) FROM proposals) AS proposals,
                (SELECT COUNT(*) FROM users WHERE role <> "owner" AND status = "active") AS freelancers,
                (SELECT COUNT(*) FROM categories WHERE is_active = 1) AS categories'
        );

        $stats = $statement->fetch() ?: [];

        return [
            'open_projects' => (int) ($stats['open_projects'] ?? 0),
            'proposals' => (int) ($stats['proposals'] ?? 0),
            'freelancers' => (int) ($stats['freelancers'] ?? 0),
            'categories' => (int) ($stats['categories'] ?? 0),
        ];
    }

    /**
     * EN: Counts records for a single user-bound query.
     * RU: Считает записи для запроса, привязанного к пользователю.
     *
     * @param string $sql SQL query / SQL-запрос
     * @param int $userId User id / ID пользователя
     * @return int
     */
    private function count(string $sql, int $userId): int
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * EN: Counts open marketplace projects.
     * RU: Считает открытые проекты маркетплейса.
     *
     * @return int
     */
    private function countOpenProjects(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM projects WHERE status = "open"')->fetchColumn();
    }

    /**
     * EN: Updates project activity timestamp after marketplace workflow events.
     * RU: Обновляет время активности проекта после событий workflow маркетплейса.
     *
     * @param int $projectId Project id / ID проекта
     * @return void
     */
    private function touchProjectActivity(int $projectId): void
    {
        $statement = $this->pdo->prepare('UPDATE projects SET last_activity_at = NOW(), updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $projectId]);
    }

    /**
     * EN: Locks and returns a proposal with project fields for workflow transitions.
     * RU: Блокирует и возвращает отклик с полями проекта для workflow-переходов.
     *
     * @param int $proposalId Proposal id / ID отклика
     * @return array<string, mixed>|null
     */
    private function proposalWithProjectForUpdate(int $proposalId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT pr.*, p.client_id, p.status AS project_status, p.accepted_proposal_id, p.assigned_freelancer_id
             FROM proposals pr
             INNER JOIN projects p ON p.id = pr.project_id
             WHERE pr.id = :proposal_id
             FOR UPDATE'
        );
        $statement->execute(['proposal_id' => $proposalId]);
        $proposal = $statement->fetch();

        return $proposal ?: null;
    }

    /**
     * EN: Locks and returns one project for completion workflow.
     * RU: Блокирует и возвращает проект для завершения workflow.
     *
     * @param int $projectId Project id / ID проекта
     * @return array<string, mixed>|null
     */
    private function findProjectForUpdate(int $projectId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM projects WHERE id = :project_id FOR UPDATE');
        $statement->execute(['project_id' => $projectId]);
        $project = $statement->fetch();

        return $project ?: null;
    }

    /**
     * EN: Shared SELECT fragment with category, client, assignee, and proposal count context.
     * RU: Общий SELECT-фрагмент с категорией, автором, исполнителем и счетчиком откликов.
     *
     * @return string
     */
    private function projectSelect(): string
    {
        return 'SELECT p.*, c.name AS category_name, c.accent_color AS category_accent,
                       u.name AS client_name, u.city AS client_city, u.email AS client_email, u.is_verified AS client_is_verified,
                       assigned.name AS assigned_freelancer_name, assigned.email AS assigned_freelancer_email,
                       (SELECT COUNT(*) FROM proposals pr WHERE pr.project_id = p.id) AS proposals_count,
                       (SELECT COUNT(*) FROM saved_projects sp WHERE sp.project_id = p.id) AS saved_count
                FROM projects p
                INNER JOIN categories c ON c.id = p.category_id
                INNER JOIN users u ON u.id = p.client_id
                LEFT JOIN users assigned ON assigned.id = p.assigned_freelancer_id';
    }
}
