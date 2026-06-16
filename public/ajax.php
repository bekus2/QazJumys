<?php
/**
 * Project: QazJumys
 * File: ajax.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Handles AJAX form actions for auth, projects, proposals, and profiles.
 * RU: Обрабатывает AJAX-действия форм для входа, проектов, откликов и профилей.
 */

declare(strict_types=1);

use QazJumys\Core\Auth;
use QazJumys\Core\Csrf;
use QazJumys\Core\Database;
use QazJumys\Core\Response;
use QazJumys\Core\Validator;
use QazJumys\Repositories\ProjectRepository;
use QazJumys\Repositories\UserRepository;

$config = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'message' => 'Әрекетке тек POST сұрауы рұқсат.'], 405);
}

if (!Csrf::validate($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    Response::json(['ok' => false, 'message' => 'Қауіпсіздік токені жарамсыз. Бетті жаңартыңыз.'], 419);
}

try {
    $pdo = Database::connection($config['database']);
} catch (Throwable) {
    Response::json(['ok' => false, 'message' => 'MySQL қосылымы дайын емес. .env және SQL импортын тексеріңіз.'], 503);
}

$action = (string) ($_POST['action'] ?? '');
$users = new UserRepository($pdo);
$projects = new ProjectRepository($pdo);

try {
    match ($action) {
        'register' => handle_register($users),
        'login' => handle_login($users),
        'logout' => handle_logout(),
        'project_create' => handle_project_create($projects),
        'proposal_create' => handle_proposal_create($projects),
        'profile_update' => handle_profile_update($users),
        default => Response::json(['ok' => false, 'message' => 'Белгісіз әрекет.'], 400),
    };
} catch (Throwable $exception) {
    $message = (bool) $config['app']['debug'] ? $exception->getMessage() : 'Сұрауды өңдеу кезінде қате пайда болды.';
    Response::json(['ok' => false, 'message' => $message], 500);
}

/**
 * EN: Ends an authenticated session through a CSRF-protected POST request.
 * RU: Завершает сессию через CSRF-защищенный POST-запрос.
 *
 * @return never
 */
function handle_logout(): never
{
    Auth::logout();
    Response::json(['ok' => true, 'message' => 'Сессия аяқталды.', 'redirect' => url_for('home')]);
}

/**
 * EN: Registers client or freelancer accounts.
 * RU: Регистрирует аккаунты заказчика или исполнителя.
 *
 * @param UserRepository $users User repository / Репозиторий пользователей
 * @return never
 */
function handle_register(UserRepository $users): never
{
    $role = (string) ($_POST['role'] ?? '');
    $name = Validator::text($_POST['name'] ?? '', 120);
    $email = Validator::text($_POST['email'] ?? '', 180);
    $password = (string) ($_POST['password'] ?? '');
    $city = Validator::text($_POST['city'] ?? '', 80);

    if (!in_array($role, ['client', 'freelancer'], true)) {
        Response::json(['ok' => false, 'message' => 'Рөлді таңдаңыз: тапсырыс беруші немесе орындаушы.'], 422);
    }

    if (mb_strlen($name, 'UTF-8') < 2 || !Validator::email($email) || mb_strlen($password, 'UTF-8') < 8) {
        Response::json(['ok' => false, 'message' => 'Аты, email және кемінде 8 таңбалы құпиясөз қажет.'], 422);
    }

    if ($users->findByEmail($email)) {
        Response::json(['ok' => false, 'message' => 'Бұл email бұрын тіркелген.'], 409);
    }

    $user = $users->create([
        'role' => $role,
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'city' => $city,
    ]);

    Auth::login($user);
    Response::json(['ok' => true, 'message' => 'Тіркелу аяқталды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Authenticates an existing account.
 * RU: Авторизует существующий аккаунт.
 *
 * @param UserRepository $users User repository / Репозиторий пользователей
 * @return never
 */
function handle_login(UserRepository $users): never
{
    $email = Validator::text($_POST['email'] ?? '', 180);
    $password = (string) ($_POST['password'] ?? '');
    $user = $users->findByEmail($email);

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        Response::json(['ok' => false, 'message' => 'Email немесе құпиясөз қате.'], 401);
    }

    Auth::login($user);
    Response::json(['ok' => true, 'message' => 'Қош келдіңіз.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Creates a new client project.
 * RU: Создает новый проект заказчика.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @return never
 */
function handle_project_create(ProjectRepository $projects): never
{
    $user = Auth::user();
    if (!$user || $user['role'] !== 'client') {
        Response::json(['ok' => false, 'message' => 'Жоба жариялау үшін тапсырыс беруші ретінде кіріңіз.'], 403);
    }

    $title = Validator::text($_POST['title'] ?? '', 180);
    $description = Validator::text($_POST['description'] ?? '', 3000);
    $projectType = (string) ($_POST['project_type'] ?? 'fixed');
    $experienceLevel = (string) ($_POST['experience_level'] ?? 'intermediate');
    $skills = Validator::text($_POST['skills'] ?? '', 500);
    $location = Validator::text($_POST['location'] ?? '', 80);
    $isRemote = isset($_POST['is_remote']) ? 1 : 0;
    $isUrgent = isset($_POST['is_urgent']) ? 1 : 0;
    $budgetMin = Validator::money($_POST['budget_min'] ?? null);
    $budgetMax = Validator::money($_POST['budget_max'] ?? null);
    $deadlineDays = max(1, min(180, (int) ($_POST['deadline_days'] ?? 7)));
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if (!in_array($projectType, ['fixed', 'hourly'], true)) {
        $projectType = 'fixed';
    }

    if (!in_array($experienceLevel, ['entry', 'intermediate', 'expert'], true)) {
        $experienceLevel = 'intermediate';
    }

    if (
        $categoryId <= 0
        || mb_strlen($title, 'UTF-8') < 6
        || mb_strlen($description, 'UTF-8') < 30
        || mb_strlen($skills, 'UTF-8') < 3
        || $budgetMin === null
        || $budgetMax === null
        || $budgetMax < $budgetMin
    ) {
        Response::json(['ok' => false, 'message' => 'Жоба атауы, сипаттамасы, дағдылары, категориясы және бюджеті дұрыс толтырылуы керек.'], 422);
    }

    $projects->create((int) $user['id'], [
        'category_id' => $categoryId,
        'title' => $title,
        'description' => $description,
        'project_type' => $projectType,
        'experience_level' => $experienceLevel,
        'skills' => $skills,
        'location' => $location,
        'is_remote' => $isRemote,
        'is_urgent' => $isUrgent,
        'budget_min' => $budgetMin,
        'budget_max' => $budgetMax,
        'deadline_days' => $deadlineDays,
    ]);

    Response::json(['ok' => true, 'message' => 'Жоба жарияланды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Creates a freelancer proposal for an open project.
 * RU: Создает отклик исполнителя на открытый проект.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @return never
 */
function handle_proposal_create(ProjectRepository $projects): never
{
    $user = Auth::user();
    if (!$user || $user['role'] !== 'freelancer') {
        Response::json(['ok' => false, 'message' => 'Отклик жіберу үшін орындаушы ретінде кіріңіз.'], 403);
    }

    $projectId = (int) ($_POST['project_id'] ?? 0);
    $coverLetter = Validator::text($_POST['cover_letter'] ?? '', 1600);
    $bidAmount = Validator::money($_POST['bid_amount'] ?? null);
    $deliveryDays = max(1, min(180, (int) ($_POST['delivery_days'] ?? 7)));

    if ($projectId <= 0 || mb_strlen($coverLetter, 'UTF-8') < 20 || $bidAmount === null) {
        Response::json(['ok' => false, 'message' => 'Ұсыныс мәтіні, баға және мерзім қажет.'], 422);
    }

    if (!$projects->isOpen($projectId)) {
        Response::json(['ok' => false, 'message' => 'Бұл жобаға ұсыныс қабылданбайды немесе жоба табылмады.'], 404);
    }

    if ($projects->hasProposal($projectId, (int) $user['id'])) {
        Response::json(['ok' => false, 'message' => 'Бұл жобаға бұрын отклик жібердіңіз.'], 409);
    }

    $projects->createProposal($projectId, (int) $user['id'], [
        'cover_letter' => $coverLetter,
        'bid_amount' => $bidAmount,
        'delivery_days' => $deliveryDays,
    ]);

    Response::json(['ok' => true, 'message' => 'Ұсыныс жіберілді.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Updates the authenticated user's public profile.
 * RU: Обновляет публичный профиль авторизованного пользователя.
 *
 * @param UserRepository $users User repository / Репозиторий пользователей
 * @return never
 */
function handle_profile_update(UserRepository $users): never
{
    $user = Auth::user();
    if (!$user) {
        Response::json(['ok' => false, 'message' => 'Алдымен жүйеге кіріңіз.'], 403);
    }

    $updated = $users->updateProfile((int) $user['id'], [
        'name' => Validator::text($_POST['name'] ?? '', 120),
        'city' => Validator::text($_POST['city'] ?? '', 80),
        'headline' => Validator::text($_POST['headline'] ?? '', 160),
        'bio' => Validator::text($_POST['bio'] ?? '', 1200),
        'skills' => Validator::text($_POST['skills'] ?? '', 600),
        'hourly_rate' => Validator::money($_POST['hourly_rate'] ?? null),
    ]);

    if ($updated) {
        Auth::login($updated);
    }

    Response::json(['ok' => true, 'message' => 'Профиль жаңартылды.', 'redirect' => url_for('profile')]);
}
