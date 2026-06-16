<?php
/**
 * Project: QazJumys
 * File: config.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Central configuration loaded from environment variables, including mail and upload limits.
 * RU: Центральная конфигурация из переменных окружения, включая почту и лимиты загрузок.
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
        'mail_enabled' => (bool) env_value('MAIL_ENABLED', false),
        'mail_from' => (string) env_value('MAIL_FROM', 'no-reply@qazjumys.local'),
        'whatsapp_default' => (string) env_value('WHATSAPP_DEFAULT', '+77075080762'),
        'upload_max_bytes' => (int) env_value('UPLOAD_MAX_BYTES', 5242880),
        'upload_allowed_mimes' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
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
