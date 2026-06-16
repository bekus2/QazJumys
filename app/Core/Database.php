<?php
/**
 * Project: QazJumys
 * File: Database.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Provides a secure PDO connection for MySQL storage.
 * RU: Предоставляет безопасное PDO-подключение к MySQL-хранилищу.
 */

declare(strict_types=1);

namespace QazJumys\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    /**
     * EN: Opens a reusable PDO connection with strict error handling.
     * RU: Открывает переиспользуемое PDO-подключение со строгой обработкой ошибок.
     *
     * @param array<string, string> $config Database config / Конфигурация базы
     * @return PDO
     */
    public static function connection(array $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('MySQL connection failed. Check .env and database/schema.sql.', 0, $exception);
        }

        return self::$connection;
    }

    /**
     * EN: Tests whether the database is reachable without leaking credentials.
     * RU: Проверяет доступность базы без раскрытия учетных данных.
     *
     * @param array<string, string> $config Database config / Конфигурация базы
     * @return bool
     */
    public static function isAvailable(array $config): bool
    {
        try {
            self::connection($config);
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }
}
