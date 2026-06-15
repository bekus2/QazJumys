<?php
/**
 * Project: QazJumys
 * File: header.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Opens the shared HTML layout and navigation.
 * RU: Открывает общий HTML-шаблон и навигацию.
 */

$currentPage = (string) ($_GET['page'] ?? 'home');
$activeUser = $user ?? \QazJumys\Core\Auth::user();
?>
<!doctype html>
<html lang="kk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(\QazJumys\Core\Csrf::token()) ?>">
    <title><?= e($pageTitle ?? 'QazJumys') ?> | QazJumys</title>
    <meta name="description" content="Қазақстандағы SMM, жарнама, сайт, SEO, CRM, 1C, дизайн және видео бағыттарына арналған фриланс порталы.">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= e($pageTitle ?? 'QazJumys') ?> | QazJumys">
    <meta property="og:description" content="Тапсырыс беруші мен орындаушыны бір жерде байланыстыратын Қазақстанға арналған digital фриланс порталы.">
    <meta property="og:type" content="website">
    <link rel="preload" href="<?= e(asset('img/hero-marketplace.png')) ?>" as="image">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body data-page="<?= e($currentPage) ?>">
<header class="site-header">
    <div class="container nav-shell">
        <a class="brand" href="<?= e(url_for('home')) ?>" aria-label="QazJumys басты беті">
            <span class="brand-mark">Q</span>
            <span>QazJumys</span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <nav id="site-menu" class="site-nav" aria-label="Негізгі мәзір">
            <a class="<?= $currentPage === 'home' ? 'is-active' : '' ?>" href="<?= e(url_for('home')) ?>">Басты бет</a>
            <a class="<?= $currentPage === 'projects' ? 'is-active' : '' ?>" href="<?= e(url_for('projects')) ?>">Жобалар</a>
            <?php if ($activeUser && $activeUser['role'] === 'client'): ?>
                <a class="<?= $currentPage === 'project-create' ? 'is-active' : '' ?>" href="<?= e(url_for('project-create')) ?>">Жоба жариялау</a>
            <?php endif; ?>
            <?php if ($activeUser): ?>
                <a class="<?= $currentPage === 'dashboard' ? 'is-active' : '' ?>" href="<?= e(url_for('dashboard')) ?>">Кабинет</a>
                <a class="<?= $currentPage === 'profile' ? 'is-active' : '' ?>" href="<?= e(url_for('profile')) ?>">Профиль</a>
                <form class="logout-form js-ajax-form" action="ajax.php" method="post" data-success-redirect="<?= e(url_for('home')) ?>">
                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">Шығу</button>
                </form>
            <?php else: ?>
                <a class="<?= $currentPage === 'login' ? 'is-active' : '' ?>" href="<?= e(url_for('login')) ?>">Кіру</a>
                <a class="nav-pill" href="<?= e(url_for('register')) ?>">Тіркелу</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
<?php if (!empty($dbNotice)): ?>
    <div class="container">
        <div class="setup-notice">
            <?= e($dbNotice) ?>
        </div>
    </div>
<?php endif; ?>
