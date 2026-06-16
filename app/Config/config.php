<?php
/**
 * Project: QazJumys
 * File: config.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Central configuration loaded from environment variables.
 * RU: Центральная конфигурация, загружаемая из переменных окружения.
 */

declare(strict_types=1);

return [
    'app' => [
        'name' => (string) env_value('APP_NAME', 'QazJumys'),
        'env' => (string) env_value('APP_ENV', 'local'),
        'debug' => (bool) env_value('APP_DEBUG', false),
        'url' => (string) env_value('APP_URL', 'http://127.0.0.1:8080'),
        'session_name' => (string) env_value('SESSION_NAME', 'qazjumys_session'),
        'mail_to' => (string) env_value('MAIL_TO', 'bek0435@gmail.com'),
        'whatsapp_default' => (string) env_value('WHATSAPP_DEFAULT', '+77075080762'),
    ],
    'database' => [
        'host' => (string) env_value('DB_HOST', '127.0.0.1'),
        'port' => (string) env_value('DB_PORT', '3306'),
        'database' => (string) env_value('DB_DATABASE', 'qazjumys_portal'),
        'username' => (string) env_value('DB_USERNAME', 'root'),
        'password' => (string) env_value('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
];
