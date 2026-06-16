<?php
/**
 * Project: QazJumys
 * File: Csrf.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Generates and validates CSRF tokens for protected requests.
 * RU: Генерирует и проверяет CSRF-токены для защищенных запросов.
 */

declare(strict_types=1);

namespace QazJumys\Core;

final class Csrf
{
    /**
     * EN: Returns a session token, creating one when missing.
     * RU: Возвращает токен сессии, создавая его при отсутствии.
     *
     * @return string
     */
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_csrf_token'];
    }

    /**
     * EN: Validates a submitted token using timing-safe comparison.
     * RU: Проверяет переданный токен с защитой от timing-атак.
     *
     * @param string|null $token Submitted token / Переданный токен
     * @return bool
     */
    public static function validate(?string $token): bool
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }
}
