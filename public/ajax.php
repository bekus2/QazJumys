<?php
/**
 * Project: QazJumys
 * File: ajax.php
 * Author: Beck Sarbassov
 * Version: 1.3.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-21
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Handles AJAX actions for auth, projects, proposals, workflow, engagement tools, messages, uploads, complaints, and owner tools.
 * RU: Обрабатывает AJAX-действия для входа, проектов, откликов, workflow, инструментов вовлечения, сообщений, файлов, жалоб и owner-панели.
 */

declare(strict_types=1);

use QazJumys\Core\Auth;
use QazJumys\Core\Csrf;
use QazJumys\Core\Database;
use QazJumys\Core\Response;
use QazJumys\Core\Upload;
use QazJumys\Core\Validator;
use QazJumys\Repositories\ComplaintRepository;
use QazJumys\Repositories\EngagementRepository;
use QazJumys\Repositories\FileRepository;
use QazJumys\Repositories\MessageRepository;
use QazJumys\Repositories\NotificationRepository;
use QazJumys\Repositories\OwnerRepository;
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
$messages = new MessageRepository($pdo);
$files = new FileRepository($pdo);
$complaints = new ComplaintRepository($pdo);
$notifications = new NotificationRepository($pdo, $config['app']);
$owner = new OwnerRepository($pdo);
$engagement = new EngagementRepository($pdo);

try {
    match ($action) {
        'register' => handle_register($users),
        'login' => handle_login($users),
        'logout' => handle_logout(),
        'project_create' => handle_project_create($projects, $notifications),
        'project_save_toggle' => handle_project_save_toggle($engagement),
        'saved_search_create' => handle_saved_search_create($engagement),
        'proposal_create' => handle_proposal_create($projects, $notifications),
        'proposal_shortlist' => handle_proposal_shortlist($projects, $notifications),
        'proposal_withdraw' => handle_proposal_withdraw($projects, $notifications),
        'proposal_accept' => handle_proposal_accept($projects, $notifications),
        'proposal_decline' => handle_proposal_decline($projects, $notifications),
        'project_cancel' => handle_project_cancel($projects, $notifications),
        'project_submit_delivery' => handle_project_submit_delivery($projects, $notifications),
        'project_complete' => handle_project_complete($projects, $notifications),
        'milestone_create' => handle_milestone_create($engagement),
        'milestone_complete' => handle_milestone_complete($engagement),
        'review_create' => handle_review_create($engagement, $notifications),
        'portfolio_create' => handle_portfolio_create($engagement),
        'portfolio_delete' => handle_portfolio_delete($engagement),
        'verification_request' => handle_verification_request($engagement, $notifications),
        'message_send' => handle_message_send($projects, $messages, $notifications),
        'file_upload' => handle_file_upload($projects, $files, $config['app']),
        'complaint_create' => handle_complaint_create($complaints, $notifications),
        'notification_mark_read' => handle_notification_mark_read($notifications),
        'profile_update' => handle_profile_update($users),
        'password_change' => handle_password_change($users),
        'owner_user_block' => handle_owner_user_block($owner, $notifications),
        'owner_user_unblock' => handle_owner_user_unblock($owner, $notifications),
        'owner_user_reset_password' => handle_owner_user_reset_password($owner, $notifications),
        'owner_complaint_update' => handle_owner_complaint_update($owner),
        'owner_project_status' => handle_owner_project_status($owner),
        'owner_verification_update' => handle_owner_verification_update($engagement, $owner, $notifications),
        default => Response::json(['ok' => false, 'message' => 'Белгісіз әрекет.'], 400),
    };
} catch (RuntimeException $exception) {
    Response::json(['ok' => false, 'message' => $exception->getMessage()], 422);
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
 * EN: Registers a unified member account that can publish projects and submit proposals.
 * RU: Регистрирует единый аккаунт участника, который может публиковать проекты и отправлять отклики.
 *
 * @param UserRepository $users User repository / Репозиторий пользователей
 * @return never
 */
function handle_register(UserRepository $users): never
{
    $name = Validator::text($_POST['name'] ?? '', 120);
    $email = Validator::text($_POST['email'] ?? '', 180);
    $password = (string) ($_POST['password'] ?? '');
    $city = Validator::text($_POST['city'] ?? '', 80);

    if (mb_strlen($name, 'UTF-8') < 2 || !Validator::email($email) || mb_strlen($password, 'UTF-8') < 8) {
        Response::json(['ok' => false, 'message' => 'Аты, email және кемінде 8 таңбалы құпиясөз қажет.'], 422);
    }

    if ($users->findByEmail($email)) {
        Response::json(['ok' => false, 'message' => 'Бұл email бұрын тіркелген.'], 409);
    }

    $user = $users->create([
        'role' => 'member',
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'city' => $city,
    ]);

    Auth::login($user);
    Response::json(['ok' => true, 'message' => 'Тіркелу аяқталды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Authenticates an existing account and blocks suspended users.
 * RU: Авторизует существующий аккаунт и запрещает вход заблокированным пользователям.
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

    if (($user['status'] ?? 'active') === 'blocked') {
        Response::json(['ok' => false, 'message' => 'Аккаунт бұғатталған. Owner-панель арқылы қолдауға жүгініңіз.'], 403);
    }

    $users->touchLogin((int) $user['id']);
    $fresh = $users->find((int) $user['id']) ?? $user;
    Auth::login($fresh);

    Response::json([
        'ok' => true,
        'message' => 'Қош келдіңіз.',
        'redirect' => (($fresh['role'] ?? '') === 'owner') ? 'owner.php' : url_for('dashboard'),
    ]);
}

/**
 * EN: Creates a new project from any active member account.
 * RU: Создает новый проект от любого активного аккаунта участника.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_project_create(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();

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

    $projectId = $projects->create((int) $user['id'], [
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

    $notifications->notify((int) $user['id'], 'project_created', 'Жоба жарияланды', 'Жобаңыз ашық маркетплейске шықты: ' . $title, false);

    Response::json(['ok' => true, 'message' => 'Жоба жарияланды.', 'redirect' => url_for('dashboard', ['project_id' => $projectId])]);
}

/**
 * EN: Toggles a saved project from the project list.
 * RU: Переключает сохранение проекта из списка проектов.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @return never
 */
function handle_project_save_toggle(EngagementRepository $engagement): never
{
    $user = require_member_account();
    $projectId = (int) ($_POST['project_id'] ?? 0);

    if ($projectId <= 0) {
        Response::json(['ok' => false, 'message' => 'Жоба табылмады.'], 422);
    }

    $saved = $engagement->toggleSavedProject((int) $user['id'], $projectId);

    Response::json([
        'ok' => true,
        'message' => $saved ? 'Жоба сақталды.' : 'Жоба сақталғандардан алынды.',
        'redirect' => url_for('projects'),
    ]);
}

/**
 * EN: Saves reusable project search filters for the current user.
 * RU: Сохраняет повторно используемые фильтры поиска проектов для текущего пользователя.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @return never
 */
function handle_saved_search_create(EngagementRepository $engagement): never
{
    $user = require_member_account();
    $label = Validator::text($_POST['label'] ?? '', 120);
    $queryString = clean_saved_search_query((string) ($_POST['query_string'] ?? ''));

    if (mb_strlen($label, 'UTF-8') < 3) {
        Response::json(['ok' => false, 'message' => 'Іздеу атауын толтырыңыз.'], 422);
    }

    $engagement->createSavedSearch((int) $user['id'], $label, $queryString);

    Response::json(['ok' => true, 'message' => 'Іздеу сақталды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Creates a proposal for an open project from any active member account.
 * RU: Создает отклик на открытый проект от любого активного аккаунта участника.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_proposal_create(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $projectId = (int) ($_POST['project_id'] ?? 0);
    $coverLetter = Validator::text($_POST['cover_letter'] ?? '', 1600);
    $bidAmount = Validator::money($_POST['bid_amount'] ?? null);
    $deliveryDays = max(1, min(180, (int) ($_POST['delivery_days'] ?? 7)));

    if ($projectId <= 0 || mb_strlen($coverLetter, 'UTF-8') < 20 || $bidAmount === null) {
        Response::json(['ok' => false, 'message' => 'Ұсыныс мәтіні, баға және мерзім қажет.'], 422);
    }

    if ($projects->hasProposal($projectId, (int) $user['id'])) {
        Response::json(['ok' => false, 'message' => 'Бұл жобаға бұрын отклик жібердіңіз.'], 409);
    }

    $proposalId = $projects->createProposal($projectId, (int) $user['id'], [
        'cover_letter' => $coverLetter,
        'bid_amount' => $bidAmount,
        'delivery_days' => $deliveryDays,
    ]);
    $proposal = $projects->findProposal($proposalId);

    if ($proposal) {
        $notifications->notify(
            (int) $proposal['client_id'],
            'proposal_created',
            'Жаңа отклик келді',
            (string) $proposal['project_title'] . ' жобасына ' . (string) $proposal['freelancer_name'] . ' ұсыныс жіберді.'
        );
    }

    Response::json(['ok' => true, 'message' => 'Ұсыныс жіберілді.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Adds a received proposal to the owner's shortlist.
 * RU: Добавляет полученный отклик в shortlist автора проекта.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_proposal_shortlist(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $proposal = $projects->shortlistProposal((int) ($_POST['proposal_id'] ?? 0), (int) $user['id']);

    $notifications->notify(
        (int) $proposal['freelancer_id'],
        'proposal_shortlisted',
        'Отклик shortlisted',
        (string) $proposal['project_title'] . ' жобасы бойынша ұсынысыңыз shortlist ішіне қосылды.'
    );

    Response::json(['ok' => true, 'message' => 'Отклик shortlist ішіне қосылды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Lets a freelancer withdraw a sent or shortlisted proposal.
 * RU: Позволяет исполнителю отозвать отправленный или shortlisted отклик.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_proposal_withdraw(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $proposal = $projects->withdrawProposal((int) ($_POST['proposal_id'] ?? 0), (int) $user['id']);

    $notifications->notify(
        (int) $proposal['client_id'],
        'proposal_withdrawn',
        'Отклик отозван',
        (string) $proposal['freelancer_name'] . ' ' . (string) $proposal['project_title'] . ' жобасы бойынша отклик қайтарып алды.',
        false
    );

    Response::json(['ok' => true, 'message' => 'Отклик қайтарылды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Accepts a proposal and starts the work phase.
 * RU: Принимает отклик и запускает рабочий этап.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_proposal_accept(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $proposal = $projects->acceptProposal((int) ($_POST['proposal_id'] ?? 0), (int) $user['id']);

    $notifications->notify(
        (int) $proposal['freelancer_id'],
        'proposal_accepted',
        'Отклик қабылданды',
        (string) $proposal['project_title'] . ' жобасы бойынша ұсынысыңыз қабылданды.'
    );

    Response::json(['ok' => true, 'message' => 'Отклик қабылданды. Жоба жұмысқа өтті.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Declines a proposal on a user's own project.
 * RU: Отклоняет отклик по собственному проекту пользователя.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_proposal_decline(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $proposal = $projects->declineProposal((int) ($_POST['proposal_id'] ?? 0), (int) $user['id']);

    $notifications->notify(
        (int) $proposal['freelancer_id'],
        'proposal_declined',
        'Отклик отклонен',
        (string) $proposal['project_title'] . ' жобасы бойынша ұсыныс қабылданбады.'
    );

    Response::json(['ok' => true, 'message' => 'Отклик отклон етілді.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Cancels an own project before completion.
 * RU: Отменяет собственный проект до завершения.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_project_cancel(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $project = $projects->cancelProject((int) ($_POST['project_id'] ?? 0), (int) $user['id']);

    if (!empty($project['assigned_freelancer_id'])) {
        $notifications->notify(
            (int) $project['assigned_freelancer_id'],
            'project_cancelled',
            'Жоба тоқтатылды',
            (string) $project['title'] . ' жобасы тоқтатылды.'
        );
    }

    Response::json(['ok' => true, 'message' => 'Жоба тоқтатылды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Lets the accepted freelancer submit delivery for review.
 * RU: Позволяет принятому исполнителю сдать работу на проверку.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_project_submit_delivery(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $project = $projects->submitDelivery((int) ($_POST['project_id'] ?? 0), (int) $user['id']);

    $notifications->notify(
        (int) $project['client_id'],
        'project_submitted',
        'Жұмыс тапсырылды',
        (string) $project['title'] . ' жобасы тексеруге дайын.'
    );

    Response::json(['ok' => true, 'message' => 'Жұмыс тексеруге тапсырылды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Lets the publisher complete accepted work.
 * RU: Позволяет автору проекта завершить принятую работу.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_project_complete(ProjectRepository $projects, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $project = $projects->completeProject((int) ($_POST['project_id'] ?? 0), (int) $user['id']);

    if (!empty($project['assigned_freelancer_id'])) {
        $notifications->notify(
            (int) $project['assigned_freelancer_id'],
            'project_completed',
            'Жоба аяқталды',
            (string) $project['title'] . ' жобасы аяқталды деп белгіленді.'
        );
    }

    Response::json(['ok' => true, 'message' => 'Жоба аяқталды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Creates a project milestone owned by the publisher.
 * RU: Создает milestone проекта со стороны автора заявки.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @return never
 */
function handle_milestone_create(EngagementRepository $engagement): never
{
    $user = require_member_account();
    $projectId = (int) ($_POST['project_id'] ?? 0);
    $title = Validator::text($_POST['title'] ?? '', 160);
    $dueDate = trim((string) ($_POST['due_date'] ?? ''));

    if ($projectId <= 0 || mb_strlen($title, 'UTF-8') < 3) {
        Response::json(['ok' => false, 'message' => 'Milestone атауын толтырыңыз.'], 422);
    }

    if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        Response::json(['ok' => false, 'message' => 'Күн форматы YYYY-MM-DD болуы керек.'], 422);
    }

    $engagement->createMilestone($projectId, (int) $user['id'], $title, $dueDate ?: null);

    Response::json(['ok' => true, 'message' => 'Milestone қосылды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Marks a visible project milestone as done.
 * RU: Отмечает видимый milestone проекта выполненным.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @return never
 */
function handle_milestone_complete(EngagementRepository $engagement): never
{
    $user = require_member_account();
    $engagement->completeMilestone((int) ($_POST['milestone_id'] ?? 0), (int) $user['id']);

    Response::json(['ok' => true, 'message' => 'Milestone орындалды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Creates a review after a completed project.
 * RU: Создает отзыв после завершенного проекта.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_review_create(EngagementRepository $engagement, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $projectId = (int) ($_POST['project_id'] ?? 0);
    $revieweeId = (int) ($_POST['reviewee_id'] ?? 0);
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
    $comment = Validator::text($_POST['comment'] ?? '', 1200);

    if ($projectId <= 0 || $revieweeId <= 0 || $revieweeId === (int) $user['id'] || mb_strlen($comment, 'UTF-8') < 10) {
        Response::json(['ok' => false, 'message' => 'Review үшін рейтинг пен толық пікір қажет.'], 422);
    }

    $engagement->createReview($projectId, (int) $user['id'], $revieweeId, $rating, $comment);
    $notifications->notify($revieweeId, 'review_created', 'Жаңа review', 'Сізге аяқталған жоба бойынша жаңа пікір қалдырылды.', false);

    Response::json(['ok' => true, 'message' => 'Review сақталды.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Adds a portfolio item to the current profile.
 * RU: Добавляет элемент портфолио в текущий профиль.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @return never
 */
function handle_portfolio_create(EngagementRepository $engagement): never
{
    $user = require_active_account();
    $title = Validator::text($_POST['title'] ?? '', 160);
    $description = Validator::text($_POST['description'] ?? '', 1200);
    $url = Validator::text($_POST['url'] ?? '', 255);
    $skills = Validator::text($_POST['skills'] ?? '', 255);

    if (mb_strlen($title, 'UTF-8') < 3 || mb_strlen($description, 'UTF-8') < 10) {
        Response::json(['ok' => false, 'message' => 'Portfolio атауы мен сипаттамасын толтырыңыз.'], 422);
    }

    if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
        Response::json(['ok' => false, 'message' => 'Portfolio URL дұрыс емес.'], 422);
    }

    $engagement->createPortfolioItem((int) $user['id'], [
        'title' => $title,
        'description' => $description,
        'url' => $url,
        'skills' => $skills,
    ]);

    Response::json(['ok' => true, 'message' => 'Portfolio қосылды.', 'redirect' => url_for('profile')]);
}

/**
 * EN: Deletes an own portfolio item.
 * RU: Удаляет собственный элемент портфолио.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @return never
 */
function handle_portfolio_delete(EngagementRepository $engagement): never
{
    $user = require_active_account();
    $engagement->deletePortfolioItem((int) ($_POST['item_id'] ?? 0), (int) $user['id']);

    Response::json(['ok' => true, 'message' => 'Portfolio элементі өшірілді.', 'redirect' => url_for('profile')]);
}

/**
 * EN: Sends a profile verification request to the owner queue.
 * RU: Отправляет заявку на верификацию профиля в очередь владельца.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_verification_request(EngagementRepository $engagement, NotificationRepository $notifications): never
{
    $user = require_active_account();
    $note = Validator::text($_POST['note'] ?? '', 1200);

    if (mb_strlen($note, 'UTF-8') < 20) {
        Response::json(['ok' => false, 'message' => 'Верификация үшін қысқаша дәлелдеме жазыңыз.'], 422);
    }

    $engagement->requestVerification((int) $user['id'], $note);
    $notifications->notify((int) $user['id'], 'verification_requested', 'Верификация жіберілді', 'Owner сіздің заявкаңызды қарайды.', false);

    Response::json(['ok' => true, 'message' => 'Верификация заявкасы жіберілді.', 'redirect' => url_for('profile')]);
}

/**
 * EN: Sends a project message between participants.
 * RU: Отправляет сообщение между участниками проекта.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param MessageRepository $messages Message repository / Репозиторий сообщений
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_message_send(ProjectRepository $projects, MessageRepository $messages, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $projectId = (int) ($_POST['project_id'] ?? 0);
    $proposalId = (int) ($_POST['proposal_id'] ?? 0) ?: null;
    $receiverId = (int) ($_POST['receiver_id'] ?? 0);
    $body = Validator::text($_POST['body'] ?? '', 1200);

    if ($projectId <= 0 || $receiverId <= 0 || mb_strlen($body, 'UTF-8') < 2) {
        Response::json(['ok' => false, 'message' => 'Хабарлама мәтіні және алушы қажет.'], 422);
    }

    if (!$projects->isParticipant($projectId, (int) $user['id']) || !$projects->isParticipant($projectId, $receiverId)) {
        Response::json(['ok' => false, 'message' => 'Бұл жоба бойынша хабарлама жіберуге рұқсат жоқ.'], 403);
    }

    $project = $projects->findProject($projectId);
    $messages->create($projectId, $proposalId, (int) $user['id'], $receiverId, $body);
    $notifications->notify($receiverId, 'message', 'Жаңа хабарлама', ($project['title'] ?? 'Жоба') . ' бойынша жаңа хабарлама келді.');

    Response::json(['ok' => true, 'message' => 'Хабарлама жіберілді.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Uploads a protected project file.
 * RU: Загружает защищенный файл проекта.
 *
 * @param ProjectRepository $projects Project repository / Репозиторий проектов
 * @param FileRepository $files File repository / Репозиторий файлов
 * @param array<string, mixed> $appConfig App config / Конфигурация приложения
 * @return never
 */
function handle_file_upload(ProjectRepository $projects, FileRepository $files, array $appConfig): never
{
    $user = require_member_account();
    $projectId = (int) ($_POST['project_id'] ?? 0);
    $proposalId = (int) ($_POST['proposal_id'] ?? 0) ?: null;
    $visibility = (string) ($_POST['visibility'] ?? 'brief');

    if (!in_array($visibility, ['brief', 'proposal', 'delivery'], true)) {
        $visibility = 'brief';
    }

    if ($projectId <= 0 || !$projects->isParticipant($projectId, (int) $user['id'])) {
        Response::json(['ok' => false, 'message' => 'Файл жүктеуге рұқсат жоқ.'], 403);
    }

    $stored = Upload::store($_FILES['project_file'] ?? [], $appConfig);
    $files->create(array_merge($stored, [
        'project_id' => $projectId,
        'proposal_id' => $proposalId,
        'uploader_id' => (int) $user['id'],
        'visibility' => $visibility,
    ]));

    Response::json(['ok' => true, 'message' => 'Файл жүктелді.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Creates a complaint for owner moderation.
 * RU: Создает жалобу для owner-модерации.
 *
 * @param ComplaintRepository $complaints Complaint repository / Репозиторий жалоб
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_complaint_create(ComplaintRepository $complaints, NotificationRepository $notifications): never
{
    $user = require_member_account();
    $subject = Validator::text($_POST['subject'] ?? '', 180);
    $body = Validator::text($_POST['body'] ?? '', 1500);

    if (mb_strlen($subject, 'UTF-8') < 4 || mb_strlen($body, 'UTF-8') < 10) {
        Response::json(['ok' => false, 'message' => 'Жалоба тақырыбы мен сипаттамасын толтырыңыз.'], 422);
    }

    $complaints->create([
        'reporter_id' => (int) $user['id'],
        'reported_user_id' => (int) ($_POST['reported_user_id'] ?? 0),
        'project_id' => (int) ($_POST['project_id'] ?? 0),
        'proposal_id' => (int) ($_POST['proposal_id'] ?? 0),
        'subject' => $subject,
        'body' => $body,
    ]);

    $notifications->notify((int) $user['id'], 'complaint_created', 'Жалоба қабылданды', 'Owner панелі жалобаңызды қарап шығады.', false);
    Response::json(['ok' => true, 'message' => 'Жалоба owner қарауына жіберілді.', 'redirect' => url_for('dashboard')]);
}

/**
 * EN: Marks notifications as read for the current user.
 * RU: Отмечает уведомления текущего пользователя как прочитанные.
 *
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_notification_mark_read(NotificationRepository $notifications): never
{
    $user = require_active_account();
    $notifications->markAllRead((int) $user['id']);

    Response::json(['ok' => true, 'message' => 'Уведомления оқылды.', 'redirect' => url_for('dashboard')]);
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
    $user = require_active_account();

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

/**
 * EN: Changes password for the authenticated account.
 * RU: Меняет пароль авторизованного аккаунта.
 *
 * @param UserRepository $users User repository / Репозиторий пользователей
 * @return never
 */
function handle_password_change(UserRepository $users): never
{
    $user = require_active_account();
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');

    if (mb_strlen($newPassword, 'UTF-8') < 10) {
        Response::json(['ok' => false, 'message' => 'Жаңа пароль кемінде 10 таңбадан тұруы керек.'], 422);
    }

    if (!$users->changePassword((int) $user['id'], $currentPassword, $newPassword)) {
        Response::json(['ok' => false, 'message' => 'Қазіргі пароль дұрыс емес.'], 422);
    }

    $fresh = $users->find((int) $user['id']);

    if ($fresh) {
        Auth::login($fresh);
    }

    Response::json(['ok' => true, 'message' => 'Пароль жаңартылды.', 'redirect' => url_for('profile')]);
}

/**
 * EN: Blocks a member from owner.php.
 * RU: Блокирует участника из owner.php.
 *
 * @param OwnerRepository $owner Owner repository / Репозиторий владельца
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_owner_user_block(OwnerRepository $owner, NotificationRepository $notifications): never
{
    $admin = require_owner_account();
    $userId = (int) ($_POST['user_id'] ?? 0);
    $reason = Validator::text($_POST['reason'] ?? 'Owner moderation', 255);

    if ($userId <= 0 || $userId === (int) $admin['id']) {
        Response::json(['ok' => false, 'message' => 'Бұл аккаунтты бұғаттау мүмкін емес.'], 422);
    }

    $owner->blockUser($userId, $reason);
    $owner->audit((int) $admin['id'], 'user_block', 'user', $userId, $reason);
    $notifications->notify($userId, 'account_blocked', 'Аккаунт бұғатталды', 'Себеп: ' . $reason, true);

    Response::json(['ok' => true, 'message' => 'Аккаунт бұғатталды.', 'redirect' => 'owner.php']);
}

/**
 * EN: Unblocks a member from owner.php.
 * RU: Разблокирует участника из owner.php.
 *
 * @param OwnerRepository $owner Owner repository / Репозиторий владельца
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_owner_user_unblock(OwnerRepository $owner, NotificationRepository $notifications): never
{
    $admin = require_owner_account();
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($userId <= 0) {
        Response::json(['ok' => false, 'message' => 'Аккаунт табылмады.'], 422);
    }

    $owner->unblockUser($userId);
    $owner->audit((int) $admin['id'], 'user_unblock', 'user', $userId, 'Account restored by owner.');
    $notifications->notify($userId, 'account_unblocked', 'Аккаунт ашылды', 'Owner аккаунтыңызды қайта қосты.', true);

    Response::json(['ok' => true, 'message' => 'Аккаунт қайта ашылды.', 'redirect' => 'owner.php']);
}

/**
 * EN: Resets a member password from owner.php.
 * RU: Сбрасывает пароль участника из owner.php.
 *
 * @param OwnerRepository $owner Owner repository / Репозиторий владельца
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_owner_user_reset_password(OwnerRepository $owner, NotificationRepository $notifications): never
{
    $admin = require_owner_account();
    $userId = (int) ($_POST['user_id'] ?? 0);
    $temporaryPassword = temporary_password();

    if ($userId <= 0 || $userId === (int) $admin['id']) {
        Response::json(['ok' => false, 'message' => 'Бұл аккаунттың паролін сброс жасауға болмайды.'], 422);
    }

    $owner->resetPassword($userId, $temporaryPassword);
    $owner->audit((int) $admin['id'], 'password_reset', 'user', $userId, 'Temporary password generated by owner.');
    $notifications->notify($userId, 'password_reset', 'Пароль сброс жасалды', 'Уақытша пароль: ' . $temporaryPassword, true);

    Response::json(['ok' => true, 'message' => 'Уақытша пароль: ' . $temporaryPassword, 'redirect' => 'owner.php']);
}

/**
 * EN: Updates complaint status from owner.php.
 * RU: Обновляет статус жалобы из owner.php.
 *
 * @param OwnerRepository $owner Owner repository / Репозиторий владельца
 * @return never
 */
function handle_owner_complaint_update(OwnerRepository $owner): never
{
    $admin = require_owner_account();
    $complaintId = (int) ($_POST['complaint_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'reviewing');
    $note = Validator::text($_POST['owner_note'] ?? '', 1200);

    if ($complaintId <= 0 || !in_array($status, ['open', 'reviewing', 'resolved', 'dismissed'], true)) {
        Response::json(['ok' => false, 'message' => 'Жалоба статусы дұрыс емес.'], 422);
    }

    $owner->updateComplaint($complaintId, $status, $note);
    $owner->audit((int) $admin['id'], 'complaint_update', 'complaint', $complaintId, $status);

    Response::json(['ok' => true, 'message' => 'Жалоба жаңартылды.', 'redirect' => 'owner.php']);
}

/**
 * EN: Updates project status from owner.php.
 * RU: Обновляет статус проекта из owner.php.
 *
 * @param OwnerRepository $owner Owner repository / Репозиторий владельца
 * @return never
 */
function handle_owner_project_status(OwnerRepository $owner): never
{
    $admin = require_owner_account();
    $projectId = (int) ($_POST['project_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');

    if ($projectId <= 0 || !in_array($status, ['open', 'in_progress', 'submitted', 'completed', 'cancelled'], true)) {
        Response::json(['ok' => false, 'message' => 'Жоба статусы дұрыс емес.'], 422);
    }

    $owner->updateProjectStatus($projectId, $status);
    $owner->audit((int) $admin['id'], 'project_status', 'project', $projectId, $status);

    Response::json(['ok' => true, 'message' => 'Жоба статусы жаңартылды.', 'redirect' => 'owner.php']);
}

/**
 * EN: Approves or rejects a profile verification request from owner.php.
 * RU: Одобряет или отклоняет заявку на верификацию из owner.php.
 *
 * @param EngagementRepository $engagement Engagement repository / Репозиторий вовлечения
 * @param OwnerRepository $owner Owner repository / Репозиторий владельца
 * @param NotificationRepository $notifications Notification repository / Репозиторий уведомлений
 * @return never
 */
function handle_owner_verification_update(EngagementRepository $engagement, OwnerRepository $owner, NotificationRepository $notifications): never
{
    $admin = require_owner_account();
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    $ownerNote = Validator::text($_POST['owner_note'] ?? '', 1200);

    if ($requestId <= 0 || !in_array($status, ['approved', 'rejected'], true)) {
        Response::json(['ok' => false, 'message' => 'Верификация статусы дұрыс емес.'], 422);
    }

    $request = $engagement->reviewVerification($requestId, $status, $ownerNote);
    $owner->audit((int) $admin['id'], 'verification_' . $status, 'verification_request', $requestId, $ownerNote);
    $notifications->notify(
        (int) $request['user_id'],
        'verification_' . $status,
        $status === 'approved' ? 'Верификация мақұлданды' : 'Верификация отклон етілді',
        $ownerNote !== '' ? $ownerNote : 'Owner верификация заявкаңызды қарады.',
        true
    );

    Response::json(['ok' => true, 'message' => 'Верификация жаңартылды.', 'redirect' => 'owner.php']);
}

/**
 * EN: Requires any authenticated non-blocked account.
 * RU: Требует любой авторизованный незаблокированный аккаунт.
 *
 * @return array<string, mixed>
 */
function require_active_account(): array
{
    $user = Auth::user();

    if (!$user) {
        Response::json(['ok' => false, 'message' => 'Алдымен жүйеге кіріңіз.'], 403);
    }

    if (($user['status'] ?? 'active') === 'blocked') {
        Response::json(['ok' => false, 'message' => 'Аккаунт бұғатталған.'], 403);
    }

    return $user;
}

/**
 * EN: Requires a normal marketplace member account.
 * RU: Требует обычный аккаунт участника маркетплейса.
 *
 * @return array<string, mixed>
 */
function require_member_account(): array
{
    $user = require_active_account();

    if (($user['role'] ?? 'member') === 'owner') {
        Response::json(['ok' => false, 'message' => 'Owner аккаунты басқаруға арналған. Нарық әрекеттері үшін қатысушы аккаунтын пайдаланыңыз.'], 403);
    }

    return $user;
}

/**
 * EN: Requires the owner account for administrative actions.
 * RU: Требует owner-аккаунт для административных действий.
 *
 * @return array<string, mixed>
 */
function require_owner_account(): array
{
    $user = require_active_account();

    if (($user['role'] ?? '') !== 'owner') {
        Response::json(['ok' => false, 'message' => 'Owner құқығы қажет.'], 403);
    }

    return $user;
}

/**
 * EN: Keeps only supported marketplace filter keys before storing a saved search.
 * RU: Оставляет только поддерживаемые ключи фильтра маркетплейса перед сохранением поиска.
 *
 * @param string $queryString Raw query string / Исходная query string
 * @return string Clean query string / Очищенная query string
 */
function clean_saved_search_query(string $queryString): string
{
    parse_str(ltrim($queryString, '?'), $parts);
    $allowedKeys = [
        'page',
        'q',
        'category_id',
        'project_type',
        'experience_level',
        'budget_min',
        'budget_max',
        'is_remote',
        'is_urgent',
        'verified_client',
        'sort',
    ];
    $clean = ['page' => 'projects'];

    foreach ($allowedKeys as $key) {
        if ($key === 'page' || !isset($parts[$key]) || is_array($parts[$key])) {
            continue;
        }

        $clean[$key] = mb_substr(Validator::text((string) $parts[$key], 160), 0, 160, 'UTF-8');
    }

    return http_build_query($clean);
}

/**
 * EN: Generates a temporary password for owner resets.
 * RU: Генерирует временный пароль для owner-сброса.
 *
 * @return string
 */
function temporary_password(): string
{
    return 'Qz!' . bin2hex(random_bytes(5)) . random_int(10, 99);
}
