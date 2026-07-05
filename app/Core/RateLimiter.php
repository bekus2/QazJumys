<?php
/**
 * Project: QazJumys
 * File: RateLimiter.php
 * Author: Beck Sarbassov
 * Version: 1.5.0
 * Release Date: 2026-07-05
 * Last Updated: 2026-07-05
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Throttles login attempts per email and per IP to slow down brute-force attacks.
 * RU: Ограничивает попытки входа по email и IP для защиты от перебора паролей.
 */

declare(strict_types=1);

namespace QazJumys\Core;

use PDO;

final class RateLimiter
{
    public const MAX_EMAIL_FAILURES = 5;
    public const MAX_IP_FAILURES = 20;
    public const WINDOW_MINUTES = 15;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Checks whether login is temporarily blocked for this email or IP.
     * RU: Проверяет, заблокирован ли временно вход для этого email или IP.
     *
     * @param string $email Normalized email / Нормализованный email
     * @param string $ip Client IP address / IP-адрес клиента
     * @return bool
     */
    public function isLoginBlocked(string $email, string $ip): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT
                (SELECT COUNT(*) FROM login_attempts
                  WHERE email = :email AND succeeded = 0
                    AND attempted_at >= (NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE)) AS email_failures,
                (SELECT COUNT(*) FROM login_attempts
                  WHERE ip_address = :ip AND succeeded = 0
                    AND attempted_at >= (NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE)) AS ip_failures'
        );
        $statement->execute(['email' => $email, 'ip' => $ip]);
        $counters = $statement->fetch() ?: [];

        return (int) ($counters['email_failures'] ?? 0) >= self::MAX_EMAIL_FAILURES
            || (int) ($counters['ip_failures'] ?? 0) >= self::MAX_IP_FAILURES;
    }

    /**
     * EN: Records a login attempt; successful logins clear previous failures for the email.
     * RU: Записывает попытку входа; успешный вход очищает предыдущие неудачи по email.
     *
     * @param string $email Normalized email / Нормализованный email
     * @param string $ip Client IP address / IP-адрес клиента
     * @param bool $succeeded Whether login succeeded / Успешен ли вход
     * @return void
     */
    public function recordLogin(string $email, string $ip, bool $succeeded): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO login_attempts (email, ip_address, succeeded, attempted_at)
             VALUES (:email, :ip, :succeeded, NOW())'
        );
        $statement->execute([
            'email' => $email,
            'ip' => $ip,
            'succeeded' => $succeeded ? 1 : 0,
        ]);

        if ($succeeded) {
            $clear = $this->pdo->prepare('DELETE FROM login_attempts WHERE email = :email AND succeeded = 0');
            $clear->execute(['email' => $email]);
        }

        $this->prune();
    }

    /**
     * EN: Removes attempts older than the retention window to keep the table small.
     * RU: Удаляет попытки старше окна хранения, чтобы таблица оставалась небольшой.
     *
     * @return void
     */
    private function prune(): void
    {
        $this->pdo->exec('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
    }
}
