<?php
/**
 * Project: QazJumys
 * File: UserRepository.php
 * Author: Beck Sarbassov
 * Version: 1.3.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-21
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Handles secure user persistence, lookup, unified member profiles, password changes, and account status.
 * RU: Отвечает за безопасное сохранение, поиск, единые профили участников, смену пароля и статус аккаунтов.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Finds a user by email for login and duplicate checks.
     * RU: Ищет пользователя по email для входа и проверки дублей.
     *
     * @param string $email Email address / Адрес email
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => mb_strtolower($email, 'UTF-8')]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * EN: Finds a user by primary key.
     * RU: Ищет пользователя по первичному ключу.
     *
     * @param int $id User id / ID пользователя
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, role, status, name, email, city, headline, bio, skills, hourly_rate, rating,
                    reviews_count, completed_projects, response_time, is_verified, password_reset_required,
                    blocked_reason, blocked_at, last_login_at, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * EN: Creates a new account with a hashed password.
     * RU: Создает новый аккаунт с хешированным паролем.
     *
     * @param array<string, mixed> $data Account data / Данные аккаунта
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (
                role, status, name, email, password_hash, city, headline, bio, skills, hourly_rate,
                rating, reviews_count, completed_projects, response_time, is_verified, password_reset_required,
                created_at, updated_at
             )
             VALUES (
                :role, "active", :name, :email, :password_hash, :city, :headline, :bio, :skills, :hourly_rate,
                0.00, 0, 0, :response_time, 0, 0, NOW(), NOW()
             )'
        );

        $statement->execute([
            'role' => $data['role'] ?? 'member',
            'name' => $data['name'],
            'email' => mb_strtolower((string) $data['email'], 'UTF-8'),
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'city' => $data['city'] ?? '',
            'headline' => $data['headline'] ?? '',
            'bio' => $data['bio'] ?? '',
            'skills' => $data['skills'] ?? '',
            'hourly_rate' => $data['hourly_rate'] ?? null,
            'response_time' => $data['response_time'] ?? '',
        ]);

        return $this->find((int) $this->pdo->lastInsertId()) ?? [];
    }

    /**
     * EN: Updates profile fields owned by the authenticated user.
     * RU: Обновляет поля профиля, принадлежащие авторизованному пользователю.
     *
     * @param int $id User id / ID пользователя
     * @param array<string, mixed> $data Profile data / Данные профиля
     * @return array<string, mixed>|null
     */
    public function updateProfile(int $id, array $data): ?array
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET name = :name,
                 city = :city,
                 headline = :headline,
                 bio = :bio,
                 skills = :skills,
                 hourly_rate = :hourly_rate,
                 updated_at = NOW()
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'city' => $data['city'],
            'headline' => $data['headline'],
            'bio' => $data['bio'],
            'skills' => $data['skills'],
            'hourly_rate' => $data['hourly_rate'],
        ]);

        return $this->find($id);
    }

    /**
     * EN: Changes the current user's password after verifying the existing password hash.
     * RU: Меняет пароль текущего пользователя после проверки существующего хеша пароля.
     *
     * @param int $id User id / ID пользователя
     * @param string $currentPassword Current password / Текущий пароль
     * @param string $newPassword New password / Новый пароль
     * @return bool True when password was changed / true если пароль изменен
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $statement = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $hash = (string) ($statement->fetchColumn() ?: '');

        if ($hash === '' || !password_verify($currentPassword, $hash)) {
            return false;
        }

        $update = $this->pdo->prepare(
            'UPDATE users
             SET password_hash = :hash, password_reset_required = 0, updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute([
            'id' => $id,
            'hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return true;
    }

    /**
     * EN: Stores the successful login timestamp for owner statistics.
     * RU: Сохраняет время успешного входа для статистики владельца.
     *
     * @param int $id User id / ID пользователя
     * @return void
     */
    public function touchLogin(int $id): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /**
     * EN: Returns an email address for notification delivery.
     * RU: Возвращает email для доставки уведомления.
     *
     * @param int $id User id / ID пользователя
     * @return array<string, mixed>|null
     */
    public function contact(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, email, status FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * EN: Returns verified freelancer profiles for public marketplace previews.
     * RU: Возвращает проверенные профили исполнителей для публичных блоков маркетплейса.
     *
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function topFreelancers(int $limit = 4): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, city, headline, skills, hourly_rate, rating, reviews_count, completed_projects, response_time, is_verified
             FROM users
             WHERE role <> "owner" AND status = "active"
             ORDER BY is_verified DESC, rating DESC, completed_projects DESC, created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
