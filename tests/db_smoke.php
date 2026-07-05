<?php
/**
 * Project: QazJumys
 * File: db_smoke.php
 * Author: Beck Sarbassov
 * Version: 1.5.0
 * Release Date: 2026-06-28
 * Last Updated: 2026-07-05
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Local database and installation smoke test for OpenServer/hosting readiness.
 * RU: Локальный smoke-тест базы данных и установки для готовности OpenServer/хостинга.
 */

declare(strict_types=1);

use QazJumys\Core\Database;

$root = dirname(__DIR__);
$failures = [];

/**
 * EN: Adds a smoke-test failure when a condition is false.
 * RU: Добавляет ошибку smoke-теста, если условие ложно.
 *
 * @param bool $condition Assertion result / Результат проверки
 * @param string $message Failure message / Сообщение ошибки
 * @return void
 */
function smoke_check(bool $condition, string $message): void
{
    global $failures;

    if (!$condition) {
        $failures[] = $message;
    }
}

$config = require $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'session'] as $extension) {
    smoke_check(extension_loaded($extension), 'Missing PHP extension: ' . $extension);
}

smoke_check(is_file($root . DIRECTORY_SEPARATOR . '.env'), '.env file is missing.');
smoke_check((string) ($config['app']['url'] ?? '') !== '', 'APP_URL is empty.');
smoke_check((bool) ($config['app']['mail_enabled'] ?? true) === false, 'MAIL_ENABLED must stay false until mail is verified.');

foreach (['uploads', 'logs', 'cache'] as $directory) {
    $path = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $directory;
    smoke_check(is_dir($path), 'Storage directory is missing: storage/' . $directory);
    smoke_check(is_writable($path), 'Storage directory is not writable: storage/' . $directory);
}

$uploadsHtaccess = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.htaccess';
smoke_check(is_file($uploadsHtaccess), 'storage/uploads/.htaccess is missing.');
smoke_check(str_contains((string) @file_get_contents($uploadsHtaccess), 'Require all denied'), 'uploads .htaccess must deny direct access.');

try {
    $pdo = Database::connection($config['database']);
    $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
    smoke_check($database === 'qazjumys_portal', 'Unexpected database: ' . $database);

    $schema = $pdo->prepare(
        'SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
         FROM information_schema.SCHEMATA
         WHERE SCHEMA_NAME = DATABASE()
         LIMIT 1'
    );
    $schema->execute();
    $schemaInfo = $schema->fetch() ?: [];
    smoke_check(($schemaInfo['DEFAULT_CHARACTER_SET_NAME'] ?? '') === 'utf8mb4', 'Database charset must be utf8mb4.');
    smoke_check(($schemaInfo['DEFAULT_COLLATION_NAME'] ?? '') === 'utf8mb4_unicode_ci', 'Database collation must be utf8mb4_unicode_ci.');

    foreach ([
        'users',
        'categories',
        'projects',
        'proposals',
        'messages',
        'project_files',
        'complaints',
        'notifications',
        'email_logs',
        'audit_logs',
        'saved_projects',
        'saved_searches',
        'project_milestones',
        'reviews',
        'portfolio_items',
        'verification_requests',
        'login_attempts',
    ] as $table) {
        $pdo->query('SELECT COUNT(*) FROM `' . $table . '`');
    }
} catch (Throwable $exception) {
    $failures[] = 'Database smoke failed: ' . $exception->getMessage();
}

if ($failures !== []) {
    fwrite(STDERR, "QazJumys DB smoke failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "QazJumys DB smoke passed.\n";
