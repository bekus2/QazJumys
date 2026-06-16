<?php
/**
 * Project: QazJumys
 * File: download.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Serves protected project uploads after checking participant or owner access.
 * RU: Отдает защищенные файлы проектов после проверки доступа участника или владельца.
 */

declare(strict_types=1);

use QazJumys\Core\Auth;
use QazJumys\Core\Database;
use QazJumys\Repositories\FileRepository;
use QazJumys\Repositories\ProjectRepository;

$config = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
$user = Auth::user();

if (!$user) {
    http_response_code(403);
    echo 'Login required.';
    exit;
}

$pdo = Database::connection($config['database']);
$files = new FileRepository($pdo);
$projects = new ProjectRepository($pdo);
$file = $files->find((int) ($_GET['id'] ?? 0));

if (!$file) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$canAccess = Auth::isOwner()
    || (int) $file['uploader_id'] === (int) $user['id']
    || $projects->isParticipant((int) $file['project_id'], (int) $user['id']);

if (!$canAccess) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

$path = PROJECT_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename((string) $file['stored_name']);

if (!is_file($path)) {
    http_response_code(404);
    echo 'Stored file missing.';
    exit;
}

header('Content-Type: ' . (string) $file['mime_type']);
header('Content-Length: ' . (string) filesize($path));
header('Content-Disposition: attachment; filename="' . rawurlencode((string) $file['original_name']) . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
