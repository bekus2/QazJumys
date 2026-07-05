<?php
/**
 * Project: QazJumys
 * File: create_owner.php
 * Author: Beck Sarbassov
 * Version: 1.5.0
 * Release Date: 2026-07-05
 * Last Updated: 2026-07-05
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: CLI installer that creates or resets the platform owner account without committing credentials to the repository.
 * RU: CLI-установщик, который создает или сбрасывает owner-аккаунт без хранения учетных данных в репозитории.
 *
 * Usage / Использование:
 *   php bin/create_owner.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('CLI only.');
}

use QazJumys\Core\Database;
use QazJumys\Core\Validator;

$config = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * EN: Reads one trimmed line from STDIN with a prompt.
 * RU: Читает одну строку из STDIN с подсказкой.
 *
 * @param string $prompt Prompt text / Текст подсказки
 * @return string
 */
function cli_prompt(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $line = fgets(STDIN);

    return $line === false ? '' : trim($line);
}

$name = cli_prompt('Owner name / Имя владельца: ');
$email = mb_strtolower(cli_prompt('Owner email: '), 'UTF-8');
$password = cli_prompt('Owner password (min 12 chars, not shown in docs): ');
$passwordRepeat = cli_prompt('Repeat password / Повторите пароль: ');

if (mb_strlen($name, 'UTF-8') < 2) {
    fwrite(STDERR, "Owner name is too short.\n");
    exit(1);
}

if (!Validator::email($email)) {
    fwrite(STDERR, "Owner email is invalid.\n");
    exit(1);
}

if (mb_strlen($password, 'UTF-8') < 12) {
    fwrite(STDERR, "Owner password must be at least 12 characters.\n");
    exit(1);
}

if ($password !== $passwordRepeat) {
    fwrite(STDERR, "Passwords do not match.\n");
    exit(1);
}

try {
    $pdo = Database::connection($config['database']);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Database connection failed: ' . $exception->getMessage() . "\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$existing = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$existing->execute(['email' => $email]);
$userId = (int) ($existing->fetchColumn() ?: 0);

if ($userId > 0) {
    $update = $pdo->prepare(
        'UPDATE users
         SET role = "owner", status = "active", name = :name, password_hash = :hash,
             is_verified = 1, password_reset_required = 0, blocked_reason = NULL, blocked_at = NULL, updated_at = NOW()
         WHERE id = :id'
    );
    $update->execute(['name' => $name, 'hash' => $hash, 'id' => $userId]);
    fwrite(STDOUT, "Owner account updated (id={$userId}).\n");
} else {
    $insert = $pdo->prepare(
        'INSERT INTO users (
            role, status, name, email, password_hash, city, headline, bio, skills,
            hourly_rate, rating, reviews_count, completed_projects, response_time,
            is_verified, password_reset_required, created_at, updated_at
         )
         VALUES (
            "owner", "active", :name, :email, :hash, "", "QazJumys owner", "", "",
            NULL, 0.00, 0, 0, NULL, 1, 0, NOW(), NOW()
         )'
    );
    $insert->execute(['name' => $name, 'email' => $email, 'hash' => $hash]);
    fwrite(STDOUT, 'Owner account created (id=' . (int) $pdo->lastInsertId() . ").\n");
}

fwrite(STDOUT, "Done. Do not store this password in any committed file.\n");
