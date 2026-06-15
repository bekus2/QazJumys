<?php
/**
 * Project: QazJumys
 * File: Auth.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Manages session-based authentication and role checks.
 * RU: Управляет сессионной аутентификацией и проверкой ролей.
 */

declare(strict_types=1);

namespace QazJumys\Core;

final class Auth
{
    /**
     * EN: Saves a minimal user identity in the session.
     * RU: Сохраняет минимальную идентичность пользователя в сессии.
     *
     * @param array<string, mixed> $user Authenticated user / Авторизованный пользователь
     * @return void
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'role' => (string) $user['role'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'city' => (string) ($user['city'] ?? ''),
        ];
    }

    /**
     * EN: Returns the active user or null for guests.
     * RU: Возвращает текущего пользователя или null для гостей.
     *
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * EN: Checks whether a user is authenticated.
     * RU: Проверяет, авторизован ли пользователь.
     *
     * @return bool
     */
    public static function check(): bool
    {
        return isset($_SESSION['user']['id']);
    }

    /**
     * EN: Checks the active user's role.
     * RU: Проверяет роль текущего пользователя.
     *
     * @param string $role Required role / Требуемая роль
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        return self::check() && ($_SESSION['user']['role'] ?? '') === $role;
    }

    /**
     * EN: Clears the session identity safely.
     * RU: Безопасно очищает идентичность пользователя в сессии.
     *
     * @return void
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();
    }
}
