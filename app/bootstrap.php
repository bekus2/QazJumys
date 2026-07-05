<?php
/**
 * Project: QazJumys
 * File: bootstrap.php
 * Author: Beck Sarbassov
 * Version: 1.5.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-07-05
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Boots configuration, sessions, security headers, logging, helpers, class loading, and shared display labels.
 * RU: Загружает конфигурацию, сессии, security-заголовки, логирование, помощники, автозагрузку классов и общие подписи.
 */

declare(strict_types=1);

define('PROJECT_ROOT', dirname(__DIR__));
define('APP_ROOT', PROJECT_ROOT . DIRECTORY_SEPARATOR . 'app');

/**
 * EN: Loads key-value variables from a local .env file without exposing secrets.
 * RU: Загружает пары ключ-значение из локального .env без раскрытия секретов.
 *
 * @param string $path Environment file path / Путь к файлу окружения
 * @return void
 */
function load_environment(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $trimmed, 2));
        $value = trim($value, "\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

/**
 * EN: Reads an environment value with a safe fallback.
 * RU: Читает значение окружения с безопасным значением по умолчанию.
 *
 * @param string $key Environment key / Ключ окружения
 * @param mixed $default Fallback value / Значение по умолчанию
 * @return mixed
 */
function env_value(string $key, mixed $default = null): mixed
{
    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true' => true,
        'false' => false,
        'null' => null,
        default => $value,
    };
}

load_environment(PROJECT_ROOT . DIRECTORY_SEPARATOR . '.env');

$config = require APP_ROOT . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'config.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'QazJumys\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    $file = APP_ROOT . DIRECTORY_SEPARATOR . $relative;

    if (is_file($file)) {
        require_once $file;
    }
});

/**
 * EN: Detects HTTPS directly or behind a reverse proxy for secure cookies.
 * RU: Определяет HTTPS напрямую или за reverse-proxy для secure-куки.
 *
 * @return bool
 */
function request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

/**
 * EN: Sends baseline security headers for all web entry points.
 * RU: Отправляет базовые security-заголовки для всех web-точек входа.
 *
 * @return void
 */
function send_security_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header(
        "Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
        . "img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'"
    );
}

/**
 * EN: Appends an application log line to storage/logs without exposing details to visitors.
 * RU: Записывает строку журнала приложения в storage/logs без раскрытия деталей посетителям.
 *
 * @param string $message Log message / Сообщение журнала
 * @param Throwable|null $exception Optional exception context / Контекст исключения
 * @return void
 */
function app_log(string $message, ?Throwable $exception = null): void
{
    $directory = PROJECT_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';

    if (!is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;

    if ($exception !== null) {
        $line .= ' :: ' . get_class($exception) . ': ' . $exception->getMessage()
            . ' @ ' . $exception->getFile() . ':' . $exception->getLine();
    }

    @file_put_contents(
        $directory . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log',
        $line . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

send_security_headers();

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = request_is_https();
    session_name((string) $config['app']['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * EN: Escapes user-controlled output for HTML contexts.
 * RU: Экранирует пользовательский вывод для HTML-контекста.
 *
 * @param mixed $value Value to escape / Значение для экранирования
 * @return string Escaped text / Экранированный текст
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * EN: Builds a public URL for the current front controller.
 * RU: Формирует публичный URL для текущего front-controller.
 *
 * @param string $page Page key / Ключ страницы
 * @param array<string, mixed> $params Query parameters / Параметры запроса
 * @return string
 */
function url_for(string $page = 'home', array $params = []): string
{
    $query = http_build_query(array_merge(['page' => $page], $params));

    return 'index.php' . ($query !== '' ? '?' . $query : '');
}

/**
 * EN: Builds a path to a public asset.
 * RU: Формирует путь к публичному файлу.
 *
 * @param string $path Asset path / Путь к файлу
 * @return string
 */
function asset(string $path): string
{
    return 'assets/' . ltrim($path, '/');
}

/**
 * EN: Formats Kazakhstan tenge amounts for compact marketplace cards.
 * RU: Форматирует суммы в тенге для компактных карточек маркетплейса.
 *
 * @param mixed $value Numeric value / Числовое значение
 * @return string
 */
function format_money(mixed $value): string
{
    return number_format((float) $value, 0, '.', ' ') . ' ₸';
}

/**
 * EN: Returns a public label for a project billing type.
 * RU: Возвращает публичную подпись типа оплаты проекта.
 *
 * @param string $type Project billing type / Тип оплаты проекта
 * @return string
 */
function project_type_label(string $type): string
{
    return match ($type) {
        'hourly' => 'Сағаттық',
        default => 'Бір жолғы',
    };
}

/**
 * EN: Returns a public label for required experience level.
 * RU: Возвращает публичную подпись требуемого уровня опыта.
 *
 * @param string $level Experience level / Уровень опыта
 * @return string
 */
function experience_level_label(string $level): string
{
    return match ($level) {
        'entry' => 'Junior',
        'expert' => 'Expert',
        default => 'Middle',
    };
}

/**
 * EN: Returns a public label for project workflow status.
 * RU: Возвращает публичную подпись статуса workflow проекта.
 *
 * @param string $status Workflow status / Статус workflow
 * @return string
 */
function project_status_label(string $status): string
{
    return match ($status) {
        'in_progress' => 'Жұмыста',
        'submitted' => 'Тексеруде',
        'completed' => 'Аяқталды',
        'cancelled' => 'Тоқтатылды',
        default => 'Ашық',
    };
}

/**
 * EN: Returns a public label for proposal status.
 * RU: Возвращает публичную подпись статуса отклика.
 *
 * @param string $status Proposal status / Статус отклика
 * @return string
 */
function proposal_status_label(string $status): string
{
    return match ($status) {
        'shortlisted' => 'Таңдауда',
        'accepted' => 'Қабылданды',
        'declined' => 'Қабылданбады',
        'withdrawn' => 'Қайтарылды',
        'completed' => 'Аяқталды',
        default => 'Жіберілді',
    };
}

/**
 * EN: Formats bytes for file lists.
 * RU: Форматирует байты для списков файлов.
 *
 * @param mixed $bytes File size / Размер файла
 * @return string
 */
function format_file_size(mixed $bytes): string
{
    $size = max(0, (float) $bytes);
    $units = ['B', 'KB', 'MB', 'GB'];

    for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }

    return number_format($size, $i === 0 ? 0 : 1, '.', ' ') . ' ' . $units[$i];
}

/**
 * EN: Converts comma-separated skills into clean display chips.
 * RU: Преобразует навыки через запятую в чистые теги для вывода.
 *
 * @param mixed $skills Comma-separated skill list / Список навыков через запятую
 * @param int $limit Maximum chips / Максимум тегов
 * @return array<int, string>
 */
function skill_chips(mixed $skills, int $limit = 5): array
{
    $parts = array_filter(array_map('trim', explode(',', (string) $skills)));

    return array_slice($parts, 0, $limit);
}

/**
 * EN: Redirects and stops execution after state-changing actions.
 * RU: Выполняет перенаправление и останавливает выполнение после изменения состояния.
 *
 * @param string $location Target URL / Целевой URL
 * @return never
 */
function redirect_to(string $location): never
{
    header('Location: ' . $location);
    exit;
}

/**
 * EN: Renders a view inside the main layout.
 * RU: Выводит представление внутри основного шаблона.
 *
 * @param string $view View filename without extension / Имя представления без расширения
 * @param array<string, mixed> $data Data for the view / Данные для представления
 * @return void
 */
function render_view(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = APP_ROOT . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $view . '.php';

    if (!is_file($viewFile)) {
        http_response_code(404);
        $pageTitle = 'Бет табылмады';
        $content = '<section class="section"><div class="container"><h1>Бет табылмады</h1></div></section>';
    } else {
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
    }

    require APP_ROOT . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
    echo $content;
    require APP_ROOT . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php';
}

return $config;
