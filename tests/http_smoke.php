<?php
/**
 * Project: QazJumys
 * File: http_smoke.php
 * Author: Beck Sarbassov
 * Version: 1.4.0
 * Release Date: 2026-06-28
 * Last Updated: 2026-06-28
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Local HTTP smoke test for public pages and direct-access protection probes.
 * RU: Локальный HTTP smoke-тест публичных страниц и проверок прямого доступа.
 */

declare(strict_types=1);

$baseUrl = rtrim((string) ($argv[1] ?? getenv('APP_URL') ?: 'http://127.0.0.1:8014'), '/');
$failures = [];

/**
 * EN: Requests a URL and returns status/body without throwing on HTTP errors.
 * RU: Запрашивает URL и возвращает статус/тело без исключений на HTTP-ошибках.
 *
 * @param string $url Absolute URL / Полный URL
 * @return array{status:int, body:string}
 */
function http_request(string $url): array
{
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 10,
            'header' => "User-Agent: QazJumysSmoke/1.4\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $status = 0;

    foreach (($http_response_header ?? []) as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
            $status = (int) $matches[1];
            break;
        }
    }

    return ['status' => $status, 'body' => (string) ($body === false ? '' : $body)];
}

/**
 * EN: Adds a smoke-test failure when a condition is false.
 * RU: Добавляет ошибку smoke-теста, если условие ложно.
 *
 * @param bool $condition Assertion result / Результат проверки
 * @param string $message Failure message / Сообщение ошибки
 * @return void
 */
function http_check(bool $condition, string $message): void
{
    global $failures;

    if (!$condition) {
        $failures[] = $message;
    }
}

foreach ([
    '/' => 200,
    '/index.php?page=projects' => 200,
    '/index.php?page=login' => 200,
    '/owner.php' => 200,
] as $path => $expectedStatus) {
    $response = http_request($baseUrl . $path);
    http_check($response['status'] === $expectedStatus, $path . ' returned HTTP ' . $response['status']);
    http_check($response['body'] !== '', $path . ' returned an empty body.');
}

$envProbe = http_request($baseUrl . '/.env');
http_check($envProbe['status'] !== 200, '.env path must not return HTTP 200.');
http_check(!str_contains($envProbe['body'], 'DB_PASSWORD'), '.env content is exposed through HTTP.');

$uploadsProbe = http_request($baseUrl . '/storage/uploads/');
http_check($uploadsProbe['status'] !== 200, 'storage/uploads is directly browsable.');

$downloadProbe = http_request($baseUrl . '/download.php?id=0');
http_check(in_array($downloadProbe['status'], [403, 404], true), 'download.php without a valid session should be denied.');

if ($failures !== []) {
    fwrite(STDERR, "QazJumys HTTP smoke failed for {$baseUrl}:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "QazJumys HTTP smoke passed for {$baseUrl}.\n";
