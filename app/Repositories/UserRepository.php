<?php
/**
 * Project: QazJumys
 * File: UserRepository.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Handles secure user persistence and lookup.
 * RU: Отвечает за безопасное сохранение и поиск пользователей.
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
        $statement = $this->pdo->prepare('SELECT id, role, name, email, city, bio, skills, created_at FROM users WHERE id = :id LIMIT 1');
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
            'INSERT INTO users (role, name, email, password_hash, city, bio, skills, created_at, updated_at)
             VALUES (:role, :name, :email, :password_hash, :city, :bio, :skills, NOW(), NOW())'
        );

        $statement->execute([
            'role' => $data['role'],
            'name' => $data['name'],
            'email' => mb_strtolower((string) $data['email'], 'UTF-8'),
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'city' => $data['city'] ?? '',
            'bio' => $data['bio'] ?? '',
            'skills' => $data['skills'] ?? '',
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
             SET name = :name, city = :city, bio = :bio, skills = :skills, updated_at = NOW()
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'city' => $data['city'],
            'bio' => $data['bio'],
            'skills' => $data['skills'],
        ]);

        return $this->find($id);
    }
}
