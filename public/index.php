<?php
/**
 * Project: QazJumys
 * File: index.php
 * Author: Beck Sarbassov
 * Version: 1.4.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-28
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Front controller for public marketplace pages, engagement features, and unified account dashboards.
 * RU: Front-controller для публичных страниц маркетплейса, функций вовлечения и единых кабинетов аккаунтов.
 */

declare(strict_types=1);

use QazJumys\Core\Auth;
use QazJumys\Core\Database;
use QazJumys\Repositories\CategoryRepository;
use QazJumys\Repositories\ComplaintRepository;
use QazJumys\Repositories\EngagementRepository;
use QazJumys\Repositories\FileRepository;
use QazJumys\Repositories\MessageRepository;
use QazJumys\Repositories\NotificationRepository;
use QazJumys\Repositories\ProjectRepository;
use QazJumys\Repositories\UserRepository;

$config = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
if (preg_match('#^/(?:\.env|app|database|storage|logs|backups)(?:/|$)#i', $requestPath)) {
    http_response_code(404);
    header('X-Content-Type-Options: nosniff');
    echo 'Not found.';
    exit;
}

$page = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['page'] ?? 'home')) ?: 'home';
$user = Auth::user();
$dbReady = false;
$dbNotice = null;
$categories = CategoryRepository::fallback();
$projects = [];
$featuredProjects = [];
$topFreelancers = [];
$recommendedProjects = [];
$marketplaceStats = [
    'open_projects' => 0,
    'proposals' => 0,
    'freelancers' => 0,
    'categories' => count($categories),
];
$projectFilters = [
    'category_id' => $_GET['category_id'] ?? null,
    'q' => trim((string) ($_GET['q'] ?? '')),
    'project_type' => $_GET['project_type'] ?? '',
    'experience_level' => $_GET['experience_level'] ?? '',
    'budget_min' => is_numeric($_GET['budget_min'] ?? null) ? (float) $_GET['budget_min'] : null,
    'budget_max' => is_numeric($_GET['budget_max'] ?? null) ? (float) $_GET['budget_max'] : null,
    'is_remote' => isset($_GET['is_remote']) ? 1 : 0,
    'is_urgent' => isset($_GET['is_urgent']) ? 1 : 0,
    'verified_client' => isset($_GET['verified_client']) ? 1 : 0,
    'sort' => $_GET['sort'] ?? 'latest',
];
$dashboardStats = ['projects' => 0, 'proposals' => 0, 'received_proposals' => 0, 'active_work' => 0, 'open_market' => 0, 'unread_messages' => 0, 'unread_notifications' => 0, 'saved_projects' => 0, 'saved_searches' => 0, 'portfolio_items' => 0, 'pending_reviews' => 0];
$profile = $user;
$myProjects = [];
$myProposals = [];
$receivedProposals = [];
$savedProjectIds = [];
$savedProjects = [];
$savedSearches = [];
$milestones = [];
$pendingReviews = [];
$reviewsReceived = [];
$portfolioItems = [];
$verificationRequest = null;
$recentMessages = [];
$recentFiles = [];
$recentComplaints = [];
$notifications = [];

try {
    $pdo = Database::connection($config['database']);
    $dbReady = true;
    $categoryRepository = new CategoryRepository($pdo);
    $projectRepository = new ProjectRepository($pdo);
    $userRepository = new UserRepository($pdo);
    $messageRepository = new MessageRepository($pdo);
    $fileRepository = new FileRepository($pdo);
    $complaintRepository = new ComplaintRepository($pdo);
    $notificationRepository = new NotificationRepository($pdo, $config['app']);
    $engagementRepository = new EngagementRepository($pdo);
    $categories = $categoryRepository->all();
    $marketplaceStats = $projectRepository->marketplaceStats();

    if ($page === 'home') {
        $projects = $projectRepository->latestOpen(6);
        $featuredProjects = $projectRepository->featuredOpen(3);
        $topFreelancers = $userRepository->topFreelancers(4);
    }

    if ($page === 'projects') {
        $projects = $projectRepository->searchOpen($projectFilters);
        $projectRepository->recordImpressions(array_column($projects, 'id'));

        if ($user && ($user['role'] ?? '') !== 'owner') {
            $savedProjectIds = $engagementRepository->savedProjectIds((int) $user['id']);
        }
    }

    if ($user && $page === 'dashboard') {
        $dashboardStats = array_merge(
            $dashboardStats,
            $projectRepository->dashboardStats((int) $user['id']),
            $engagementRepository->engagementStats((int) $user['id'])
        );
        $dashboardStats['unread_messages'] = $messageRepository->unreadCount((int) $user['id']);
        $dashboardStats['unread_notifications'] = $notificationRepository->unreadCount((int) $user['id']);
        $profile = $userRepository->find((int) $user['id']) ?? $user;
        $myProjects = $projectRepository->byClient((int) $user['id']);
        $myProposals = $projectRepository->proposalsByFreelancer((int) $user['id']);
        $receivedProposals = $projectRepository->proposalsForClient((int) $user['id']);
        $recommendedProjects = $projectRepository->latestOpen(3);
        $savedProjects = $engagementRepository->savedProjects((int) $user['id']);
        $savedSearches = $engagementRepository->savedSearches((int) $user['id']);
        $milestones = $engagementRepository->milestonesForUser((int) $user['id']);
        $pendingReviews = $engagementRepository->pendingReviews((int) $user['id']);
        $reviewsReceived = $engagementRepository->reviewsForUser((int) $user['id']);
        $portfolioItems = $engagementRepository->portfolioItems((int) $user['id']);
        $verificationRequest = $engagementRepository->latestVerificationForUser((int) $user['id']);
        $recentMessages = $messageRepository->recentForUser((int) $user['id']);
        $recentFiles = $fileRepository->recentForUser((int) $user['id']);
        $recentComplaints = $complaintRepository->byReporter((int) $user['id']);
        $notifications = $notificationRepository->recentForUser((int) $user['id']);
    }

    if ($user && $page === 'profile') {
        $profile = $userRepository->find((int) $user['id']) ?? $user;
        $portfolioItems = $engagementRepository->portfolioItems((int) $user['id']);
        $reviewsReceived = $engagementRepository->reviewsForUser((int) $user['id']);
        $verificationRequest = $engagementRepository->latestVerificationForUser((int) $user['id']);
    }
} catch (Throwable $exception) {
    $dbNotice = 'MySQL баптауы аяқталмаған. .env файлын жасап, database/schema.sql және database/seed.sql импорттаңыз.';
}

$protectedPages = ['dashboard', 'project-create', 'profile'];
if (in_array($page, $protectedPages, true) && !$user) {
    redirect_to(url_for('login'));
}

if ($page === 'project-create' && $user && ($user['role'] ?? '') === 'owner') {
    redirect_to(url_for('dashboard'));
}

$routes = [
    'home' => ['view' => 'home', 'title' => 'Қазақстан фриланс порталы'],
    'register' => ['view' => 'auth-register', 'title' => 'Тіркелу'],
    'login' => ['view' => 'auth-login', 'title' => 'Кіру'],
    'projects' => ['view' => 'projects', 'title' => 'Жобалар'],
    'project-create' => ['view' => 'project-create', 'title' => 'Жоба жариялау'],
    'dashboard' => ['view' => 'dashboard', 'title' => 'Жеке кабинет'],
    'profile' => ['view' => 'profile', 'title' => 'Профиль'],
];

$route = $routes[$page] ?? $routes['home'];

render_view($route['view'], [
    'pageTitle' => $route['title'],
    'config' => $config,
    'user' => $user,
    'dbReady' => $dbReady,
    'dbNotice' => $dbNotice,
    'categories' => $categories,
    'projects' => $projects,
    'featuredProjects' => $featuredProjects,
    'topFreelancers' => $topFreelancers,
    'recommendedProjects' => $recommendedProjects,
    'marketplaceStats' => $marketplaceStats,
    'projectFilters' => $projectFilters,
    'dashboardStats' => $dashboardStats,
    'profile' => $profile,
    'myProjects' => $myProjects,
    'myProposals' => $myProposals,
    'receivedProposals' => $receivedProposals,
    'savedProjectIds' => $savedProjectIds,
    'savedProjects' => $savedProjects,
    'savedSearches' => $savedSearches,
    'milestones' => $milestones,
    'pendingReviews' => $pendingReviews,
    'reviewsReceived' => $reviewsReceived,
    'portfolioItems' => $portfolioItems,
    'verificationRequest' => $verificationRequest,
    'recentMessages' => $recentMessages,
    'recentFiles' => $recentFiles,
    'recentComplaints' => $recentComplaints,
    'notifications' => $notifications,
]);
