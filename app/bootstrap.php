<?php
/**
 * Project: QazJumys
 * File: bootstrap.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Boots configuration, sessions, helpers, and class loading.
 * RU: Загружает конфигурацию, сессии, помощники и автозагрузку классов.
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

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
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
