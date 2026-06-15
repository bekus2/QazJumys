<?php
/**
 * Project: QazJumys
 * File: index.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Front controller for public pages.
 * RU: Front-controller для публичных страниц.
 */

declare(strict_types=1);

use QazJumys\Core\Auth;
use QazJumys\Core\Database;
use QazJumys\Repositories\CategoryRepository;
use QazJumys\Repositories\ProjectRepository;
use QazJumys\Repositories\UserRepository;

$config = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$page = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['page'] ?? 'home')) ?: 'home';
$user = Auth::user();
$dbReady = false;
$dbNotice = null;
$categories = CategoryRepository::fallback();
$projects = [];
$dashboardStats = ['projects' => 0, 'proposals' => 0, 'open_market' => 0];
$profile = $user;
$myProjects = [];
$myProposals = [];

try {
    $pdo = Database::connection($config['database']);
    $dbReady = true;
    $categoryRepository = new CategoryRepository($pdo);
    $projectRepository = new ProjectRepository($pdo);
    $userRepository = new UserRepository($pdo);
    $categories = $categoryRepository->all();

    if ($page === 'home') {
        $projects = $projectRepository->latestOpen(6);
    }

    if ($page === 'projects') {
        $projects = $projectRepository->searchOpen([
            'category_id' => $_GET['category_id'] ?? null,
            'q' => trim((string) ($_GET['q'] ?? '')),
        ]);
    }

    if ($user && $page === 'dashboard') {
        $dashboardStats = $projectRepository->dashboardStats((int) $user['id'], (string) $user['role']);
        $profile = $userRepository->find((int) $user['id']) ?? $user;
        $myProjects = $user['role'] === 'client' ? $projectRepository->byClient((int) $user['id']) : [];
        $myProposals = $user['role'] === 'freelancer' ? $projectRepository->proposalsByFreelancer((int) $user['id']) : [];
    }

    if ($user && $page === 'profile') {
        $profile = $userRepository->find((int) $user['id']) ?? $user;
    }
} catch (Throwable $exception) {
    $dbNotice = 'MySQL баптауы аяқталмаған. .env файлын жасап, database/schema.sql және database/seed.sql импорттаңыз.';
}

$protectedPages = ['dashboard', 'project-create', 'profile'];
if (in_array($page, $protectedPages, true) && !$user) {
    redirect_to(url_for('login'));
}

if ($page === 'project-create' && $user && $user['role'] !== 'client') {
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
    'dashboardStats' => $dashboardStats,
    'profile' => $profile,
    'myProjects' => $myProjects,
    'myProposals' => $myProposals,
]);
