<?php
/**
 * Project: QazJumys
 * File: Response.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Sends normalized JSON responses for AJAX requests.
 * RU: Отправляет нормализованные JSON-ответы для AJAX-запросов.
 */

declare(strict_types=1);

namespace QazJumys\Core;

final class Response
{
    /**
     * EN: Outputs JSON and ends request execution.
     * RU: Выводит JSON и завершает выполнение запроса.
     *
     * @param array<string, mixed> $payload Response body / Тело ответа
     * @param int $status HTTP status / HTTP-статус
     * @return never
     */
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
