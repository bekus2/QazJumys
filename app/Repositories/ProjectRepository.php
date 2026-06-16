<?php
/**
 * Project: QazJumys
 * File: ProjectRepository.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Stores projects, proposal bids, dashboard counters, and marketplace discovery data.
 * RU: Хранит проекты, отклики, показатели кабинета и данные поиска маркетплейса.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

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
     * EN: Searches open projects by keyword, category, budget, type, experience level, and sort order.
     * RU: Ищет открытые проекты по слову, категории, бюджету, типу, уровню опыта и сортировке.
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
            $where[] = '(p.title LIKE :query OR p.description LIKE :query OR p.skills LIKE :query)';
            $params['query'] = '%' . $filters['q'] . '%';
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

        $orderBy = match ((string) ($filters['sort'] ?? 'latest')) {
            'budget_high' => 'p.budget_max DESC, p.created_at DESC',
            'budget_low' => 'p.budget_min ASC, p.created_at DESC',
            'deadline_soon' => 'p.deadline_days ASC, p.created_at DESC',
            'proposals_low' => 'proposals_count ASC, p.created_at DESC',
            default => 'p.is_featured DESC, p.is_urgent DESC, p.created_at DESC',
        };

        $sql = $this->projectSelect() . '
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY ' . $orderBy;

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * EN: Creates a client project after validation.
     * RU: Создает проект заказчика после проверки данных.
     *
     * @param int $clientId Client user id / ID заказчика
     * @param array<string, mixed> $data Project data / Данные проекта
     * @return int
     */
    public function create(int $clientId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO projects (
                client_id, category_id, title, description, project_type, experience_level, skills, location,
                is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
             )
             VALUES (
                :client_id, :category_id, :title, :description, :project_type, :experience_level, :skills, :location,
                :is_remote, 0, :is_urgent, :budget_min, :budget_max, :deadline_days, "open", NOW(), NOW()
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
     * EN: Returns projects owned by a client with proposal counters.
     * RU: Возвращает проекты заказчика со счетчиками откликов.
     *
     * @param int $clientId Client user id / ID заказчика
     * @return array<int, array<string, mixed>>
     */
    public function byClient(int $clientId): array
    {
        $statement = $this->pdo->prepare(
            $this->projectSelect() . '
             WHERE p.client_id = :client_id
             ORDER BY p.created_at DESC'
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll();
    }

    /**
     * EN: Returns proposals submitted by a freelancer with related project context.
     * RU: Возвращает отклики исполнителя вместе с контекстом проекта.
     *
     * @param int $freelancerId Freelancer user id / ID исполнителя
     * @return array<int, array<string, mixed>>
     */
    public function proposalsByFreelancer(int $freelancerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT pr.*, p.title AS project_title, p.status AS project_status, p.project_type,
                    p.experience_level, p.budget_min, p.budget_max, c.name AS category_name,
                    u.name AS client_name
             FROM proposals pr
             INNER JOIN projects p ON p.id = pr.project_id
             INNER JOIN categories c ON c.id = p.category_id
             INNER JOIN users u ON u.id = p.client_id
             WHERE pr.freelancer_id = :freelancer_id
             ORDER BY pr.created_at DESC'
        );
        $statement->execute(['freelancer_id' => $freelancerId]);

        return $statement->fetchAll();
    }

    /**
     * EN: Checks whether a project can still receive freelancer proposals.
     * RU: Проверяет, может ли проект принимать отклики исполнителей.
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
     * EN: Creates a proposal bid for an open project.
     * RU: Создает отклик на открытый проект.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $freelancerId Freelancer id / ID исполнителя
     * @param array<string, mixed> $data Proposal data / Данные отклика
     * @return int
     */
    public function createProposal(int $projectId, int $freelancerId, array $data): int
    {
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

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Prevents duplicate freelancer proposals for the same project.
     * RU: Предотвращает повторные отклики исполнителя на один проект.
     *
     * @param int $projectId Project id / ID проекта
     * @param int $freelancerId Freelancer id / ID исполнителя
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
     * EN: Calculates dashboard counters for the authenticated role.
     * RU: Считает показатели кабинета для текущей роли.
     *
     * @param int $userId User id / ID пользователя
     * @param string $role Role key / Ключ роли
     * @return array<string, int>
     */
    public function dashboardStats(int $userId, string $role): array
    {
        if ($role === 'client') {
            $projectCount = $this->count('SELECT COUNT(*) FROM projects WHERE client_id = :id', $userId);
            $proposalCount = $this->count(
                'SELECT COUNT(*) FROM proposals pr INNER JOIN projects p ON p.id = pr.project_id WHERE p.client_id = :id',
                $userId
            );

            return [
                'projects' => $projectCount,
                'proposals' => $proposalCount,
                'open_market' => $this->countOpenProjects(),
            ];
        }

        $proposalCount = $this->count('SELECT COUNT(*) FROM proposals WHERE freelancer_id = :id', $userId);

        return [
            'projects' => $this->countOpenProjects(),
            'proposals' => $proposalCount,
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
                (SELECT COUNT(*) FROM users WHERE role = "freelancer") AS freelancers,
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
     * EN: Shared SELECT fragment with category, client, and proposal count context.
     * RU: Общий SELECT-фрагмент с категорией, заказчиком и счетчиком откликов.
     *
     * @return string
     */
    private function projectSelect(): string
    {
        return 'SELECT p.*, c.name AS category_name, c.accent_color AS category_accent,
                       u.name AS client_name, u.city AS client_city,
                       (SELECT COUNT(*) FROM proposals pr WHERE pr.project_id = p.id) AS proposals_count
                FROM projects p
                INNER JOIN categories c ON c.id = p.category_id
                INNER JOIN users u ON u.id = p.client_id';
    }
}
