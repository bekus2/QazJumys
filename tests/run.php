<?php
/**
 * Project: QazJumys
 * File: run.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Lightweight CI test runner for PHP syntax, required docs, and schema feature checks.
 * RU: Легкий CI runner для проверки PHP-синтаксиса, обязательной документации и возможностей SQL-схемы.
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
] as $needle) {
    assert_check(str_contains($schema, $needle), 'Schema does not contain required token: ' . $needle);
}

assert_check(is_file($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'owner.php'), 'owner.php is missing.');
assert_check(is_file($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'download.php'), 'download.php is missing.');

if ($failures !== []) {
    fwrite(STDERR, "QazJumys CI checks failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "QazJumys CI checks passed.\n";
