<?php
/**
 * Project: QazJumys
 * File: ProjectRepository.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Stores projects, proposal bids, and dashboard marketplace data.
 * RU: Хранит проекты, отклики и данные маркетплейса для кабинета.
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
     * EN: Returns recently opened projects for the homepage.
     * RU: Возвращает свежие открытые проекты для главной страницы.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function latestOpen(int $limit = 6): array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name, u.name AS client_name,
                    COUNT(pr.id) AS proposals_count
             FROM projects p
             INNER JOIN categories c ON c.id = p.category_id
             INNER JOIN users u ON u.id = p.client_id
             LEFT JOIN proposals pr ON pr.project_id = p.id
             WHERE p.status = "open"
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Searches open projects by keyword and category.
     * RU: Ищет открытые проекты по ключевому слову и категории.
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
            $where[] = '(p.title LIKE :query OR p.description LIKE :query)';
            $params['query'] = '%' . $filters['q'] . '%';
        }

        $sql = 'SELECT p.*, c.name AS category_name, u.name AS client_name,
                       COUNT(pr.id) AS proposals_count
                FROM projects p
                INNER JOIN categories c ON c.id = p.category_id
                INNER JOIN users u ON u.id = p.client_id
                LEFT JOIN proposals pr ON pr.project_id = p.id
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY p.id
                ORDER BY p.created_at DESC';

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
            'INSERT INTO projects (client_id, category_id, title, description, budget_min, budget_max, deadline_days, status, created_at, updated_at)
             VALUES (:client_id, :category_id, :title, :description, :budget_min, :budget_max, :deadline_days, "open", NOW(), NOW())'
        );

        $statement->execute([
            'client_id' => $clientId,
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'budget_min' => $data['budget_min'],
            'budget_max' => $data['budget_max'],
            'deadline_days' => (int) $data['deadline_days'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * EN: Returns projects owned by a client.
     * RU: Возвращает проекты, принадлежащие заказчику.
     *
     * @param int $clientId Client user id / ID заказчика
     * @return array<int, array<string, mixed>>
     */
    public function byClient(int $clientId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name, COUNT(pr.id) AS proposals_count
             FROM projects p
             INNER JOIN categories c ON c.id = p.category_id
             LEFT JOIN proposals pr ON pr.project_id = p.id
             WHERE p.client_id = :client_id
             GROUP BY p.id
             ORDER BY p.created_at DESC'
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll();
    }

    /**
     * EN: Returns proposals submitted by a freelancer.
     * RU: Возвращает отклики, отправленные исполнителем.
     *
     * @param int $freelancerId Freelancer user id / ID исполнителя
     * @return array<int, array<string, mixed>>
     */
    public function proposalsByFreelancer(int $freelancerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT pr.*, p.title AS project_title, p.status AS project_status, c.name AS category_name
             FROM proposals pr
             INNER JOIN projects p ON p.id = pr.project_id
             INNER JOIN categories c ON c.id = p.category_id
             WHERE pr.freelancer_id = :freelancer_id
             ORDER BY pr.created_at DESC'
        );
        $statement->execute(['freelancer_id' => $freelancerId]);

        return $statement->fetchAll();
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

            return ['projects' => $projectCount, 'proposals' => $proposalCount, 'open_market' => $this->countOpenProjects()];
        }

        $proposalCount = $this->count('SELECT COUNT(*) FROM proposals WHERE freelancer_id = :id', $userId);

        return ['projects' => $this->countOpenProjects(), 'proposals' => $proposalCount, 'open_market' => $this->countOpenProjects()];
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
}
