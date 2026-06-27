<?php
/**
 * Project: QazJumys
 * File: run.php
 * Author: Beck Sarbassov
 * Version: 1.4.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-28
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Lightweight CI test runner for syntax, required docs, schema checks, and v1.4 security/workflow coverage.
 * RU: Легкий CI runner для синтаксиса, обязательной документации, SQL-схемы и покрытия security/workflow v1.4.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

/**
 * EN: Records a failed assertion.
 * RU: Записывает неуспешную проверку.
 *
 * @param bool $condition Assertion result / Результат проверки
 * @param string $message Failure message / Сообщение ошибки
 * @return void
 */
function assert_check(bool $condition, string $message): void
{
    global $failures;

    if (!$condition) {
        $failures[] = $message;
    }
}

/**
 * EN: Returns all project PHP files that must pass syntax checks.
 * RU: Возвращает все PHP-файлы проекта, которые должны пройти проверку синтаксиса.
 *
 * @param string $root Project root / Корень проекта
 * @return array<int, string>
 */
function php_files(string $root): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

foreach (php_files($root) as $file) {
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file);
    exec($command, $output, $code);
    assert_check($code === 0, 'PHP lint failed: ' . $file . ' :: ' . implode(' ', $output));
}

foreach (['README.md', 'HANDOFF.md', 'PROJECT_CONTEXT.md', 'Codex_History.md', 'TASK.md', 'AI_RULES.md'] as $doc) {
    assert_check(is_file($root . DIRECTORY_SEPARATOR . $doc), 'Missing required documentation file: ' . $doc);
}

$schema = (string) file_get_contents($root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema.sql');
foreach ([
    "role ENUM('member', 'owner', 'client', 'freelancer')",
    "status ENUM('open', 'in_progress', 'submitted', 'completed', 'cancelled')",
    'CREATE TABLE IF NOT EXISTS messages',
    'CREATE TABLE IF NOT EXISTS project_files',
    'CREATE TABLE IF NOT EXISTS complaints',
    'CREATE TABLE IF NOT EXISTS notifications',
    'CREATE TABLE IF NOT EXISTS audit_logs',
    'views_count INT UNSIGNED NOT NULL DEFAULT 0',
    'last_activity_at DATETIME NULL',
    'CREATE TABLE IF NOT EXISTS saved_projects',
    'CREATE TABLE IF NOT EXISTS saved_searches',
    'CREATE TABLE IF NOT EXISTS project_milestones',
    'CREATE TABLE IF NOT EXISTS reviews',
    'CREATE TABLE IF NOT EXISTS portfolio_items',
    'CREATE TABLE IF NOT EXISTS verification_requests',
] as $needle) {
    assert_check(str_contains($schema, $needle), 'Schema does not contain required token: ' . $needle);
}

assert_check(is_file($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'owner.php'), 'owner.php is missing.');
assert_check(is_file($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'download.php'), 'download.php is missing.');
assert_check(is_file($root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR . 'EngagementRepository.php'), 'EngagementRepository.php is missing.');
assert_check(is_file($root . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'db_smoke.php'), 'db_smoke.php is missing.');
assert_check(is_file($root . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'http_smoke.php'), 'http_smoke.php is missing.');
assert_check(is_file($root . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'workflow_smoke.php'), 'workflow_smoke.php is missing.');

$ajax = (string) file_get_contents($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'ajax.php');
foreach ([
    'project_save_toggle',
    'saved_search_create',
    'proposal_shortlist',
    'proposal_withdraw',
    'project_cancel',
    'milestone_create',
    'review_create',
    'portfolio_create',
    'verification_request',
    'owner_verification_update',
    'password_change',
] as $action) {
    assert_check(str_contains($ajax, $action), 'ajax.php does not contain action: ' . $action);
}

$projectRepository = (string) file_get_contents($root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR . 'ProjectRepository.php');
foreach ([
    'public function canMessage(',
    'pr.status IN ("sent", "shortlisted", "accepted", "completed")',
    'accepted_proposal_id IS NULL',
] as $needle) {
    assert_check(str_contains($projectRepository, $needle), 'ProjectRepository.php does not contain required token: ' . $needle);
}

$fileRepository = (string) file_get_contents($root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR . 'FileRepository.php');
foreach ([
    'public function canAccess(',
    'public function canUpload(',
    'f.visibility = "delivery"',
    'f.visibility = "proposal"',
    'f.visibility = "brief"',
] as $needle) {
    assert_check(str_contains($fileRepository, $needle), 'FileRepository.php does not contain required token: ' . $needle);
}

$download = (string) file_get_contents($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'download.php');
assert_check(str_contains($download, 'canAccess('), 'download.php must use FileRepository access checks.');

$readme = (string) file_get_contents($root . DIRECTORY_SEPARATOR . 'README.md');
assert_check(!str_contains($readme, '0123456789+Aa'), 'README.md must not expose the initial owner password.');
assert_check(!str_contains($readme, 'Default Owner Account'), 'README.md must not publish owner credential block.');

if ($failures !== []) {
    fwrite(STDERR, "QazJumys CI checks failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "QazJumys CI checks passed.\n";
