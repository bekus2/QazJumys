<?php
/**
 * Project: QazJumys
 * File: owner.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Protected owner administration panel for statistics, moderation, account control, complaints, and operations logs.
 * RU: Защищенная owner-панель для статистики, модерации, управления аккаунтами, жалоб и операционных журналов.
 */

declare(strict_types=1);

use QazJumys\Core\Auth;
use QazJumys\Core\Csrf;
use QazJumys\Core\Database;
use QazJumys\Repositories\OwnerRepository;

$config = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
$user = Auth::user();
$dbNotice = null;
$stats = [];
$users = [];
$projects = [];
$complaints = [];
$emailLogs = [];
$auditLogs = [];

try {
    $pdo = Database::connection($config['database']);
    $ownerRepository = new OwnerRepository($pdo);

    if ($user && Auth::isOwner()) {
        $stats = $ownerRepository->stats();
        $users = $ownerRepository->users();
        $projects = $ownerRepository->projects();
        $complaints = $ownerRepository->complaints();
        $emailLogs = $ownerRepository->emailLogs();
        $auditLogs = $ownerRepository->auditLogs();
    }
} catch (Throwable) {
    $dbNotice = 'MySQL қосылымы дайын емес. .env және SQL импортын тексеріңіз.';
}

$title = 'Owner Panel | QazJumys';
?>
<!doctype html>
<html lang="kk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($title) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="owner-body">
<header class="site-header owner-header">
    <div class="container nav-shell">
        <a class="brand" href="<?= e(url_for('home')) ?>">
            <span class="brand-mark">Q</span>
            <span class="brand-text">QazJumys Owner</span>
        </a>
        <nav class="site-nav owner-nav" aria-label="Owner menu">
            <a href="<?= e(url_for('dashboard')) ?>">Кабинет</a>
            <a href="<?= e(url_for('home')) ?>">Сайт</a>
            <?php if ($user): ?>
                <form class="logout-form js-ajax-form" action="ajax.php" method="post">
                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">Шығу</button>
                </form>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
<?php if ($dbNotice): ?>
    <section class="auth-section">
        <div class="container setup-notice"><?= e($dbNotice) ?></div>
    </section>
<?php elseif (!$user): ?>
    <section class="auth-section">
        <div class="container auth-grid compact">
            <div class="auth-copy">
                <span class="eyebrow">Owner login</span>
                <h1>Owner panel</h1>
                <p>Статистика, модерация, жалобы, аккаунты, email-журнал және audit-log бір жерде.</p>
            </div>
            <form class="panel form js-ajax-form" action="ajax.php" method="post" data-success-redirect="owner.php">
                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="action" value="login">
                <label>
                    <span>Email</span>
                    <input type="email" name="email" required maxlength="180" autocomplete="email">
                </label>
                <label>
                    <span>Құпиясөз</span>
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button class="btn btn-primary btn-full" type="submit">Owner кіру</button>
            </form>
        </div>
    </section>
<?php elseif (!Auth::isOwner()): ?>
    <section class="auth-section">
        <div class="container empty-state">
            <h1>403</h1>
            <p>Бұл бет тек owner аккаунтына арналған.</p>
            <a class="btn btn-primary" href="<?= e(url_for('dashboard')) ?>">Кабинетке қайту</a>
        </div>
    </section>
<?php else: ?>
    <section class="page-hero owner-hero">
        <div class="container dashboard-head">
            <div>
                <span class="eyebrow">Owner operations</span>
                <h1>Басқару панелі</h1>
                <p>Платформа статистикасы, пайдаланушылар, шағымдар, жобалар, email-log және audit-log.</p>
            </div>
            <div class="page-stat">
                <strong><?= (int) ($stats['complaints_open'] ?? 0) ?></strong>
                <span>ашық шағым</span>
            </div>
        </div>
    </section>

    <section class="section section-tight">
        <div class="container stat-grid owner-stat-grid">
            <div class="stat-card"><span>Қатысушылар</span><strong><?= (int) $stats['members_total'] ?></strong></div>
            <div class="stat-card"><span>Бұғатталған</span><strong><?= (int) $stats['blocked_users'] ?></strong></div>
            <div class="stat-card"><span>Барлық жобалар</span><strong><?= (int) $stats['projects_total'] ?></strong></div>
            <div class="stat-card"><span>Ашық</span><strong><?= (int) $stats['projects_open'] ?></strong></div>
            <div class="stat-card"><span>Жұмыста</span><strong><?= (int) $stats['projects_active'] ?></strong></div>
            <div class="stat-card"><span>Тексеруде</span><strong><?= (int) $stats['projects_submitted'] ?></strong></div>
            <div class="stat-card"><span>Аяқталған</span><strong><?= (int) $stats['projects_completed'] ?></strong></div>
            <div class="stat-card"><span>Откликтер</span><strong><?= (int) $stats['proposals_total'] ?></strong></div>
            <div class="stat-card"><span>Хабарламалар</span><strong><?= (int) $stats['messages_total'] ?></strong></div>
            <div class="stat-card"><span>Файлдар</span><strong><?= (int) $stats['files_total'] ?></strong></div>
            <div class="stat-card stat-wide"><span>Аяқталған бюджет</span><strong><?= e(format_money($stats['completed_budget'])) ?></strong></div>
        </div>
    </section>

    <section class="section">
        <div class="container owner-grid">
            <section class="panel owner-panel owner-panel-wide">
                <div class="section-heading">
                    <span class="eyebrow">Users</span>
                    <h2>Аккаунттар</h2>
                </div>
                <div class="table-wrap">
                    <table class="owner-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пайдаланушы</th>
                            <th>Статус</th>
                            <th>Жоба / отклик</th>
                            <th>Әрекет</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $account): ?>
                            <tr>
                                <td><?= (int) $account['id'] ?></td>
                                <td>
                                    <strong><?= e($account['name']) ?></strong>
                                    <span><?= e($account['email']) ?></span>
                                </td>
                                <td>
                                    <span class="status-pill status-<?= e($account['status']) ?>"><?= e($account['status']) ?></span>
                                    <span><?= e($account['role']) ?></span>
                                </td>
                                <td><?= (int) $account['projects_count'] ?> / <?= (int) $account['proposals_count'] ?></td>
                                <td>
                                    <?php if (($account['role'] ?? '') !== 'owner'): ?>
                                        <div class="owner-actions">
                                            <?php if (($account['status'] ?? '') === 'blocked'): ?>
                                                <form class="js-ajax-form" action="ajax.php" method="post">
                                                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                                                    <input type="hidden" name="action" value="owner_user_unblock">
                                                    <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                                                    <button class="btn btn-small" type="submit">Разблок</button>
                                                </form>
                                            <?php else: ?>
                                                <form class="owner-inline-form js-ajax-form" action="ajax.php" method="post">
                                                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                                                    <input type="hidden" name="action" value="owner_user_block">
                                                    <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                                                    <input type="text" name="reason" maxlength="255" placeholder="Себеп">
                                                    <button class="btn btn-small" type="submit">Блок</button>
                                                </form>
                                            <?php endif; ?>
                                            <form class="js-ajax-form" action="ajax.php" method="post">
                                                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                                                <input type="hidden" name="action" value="owner_user_reset_password">
                                                <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                                                <button class="btn btn-small" type="submit">Reset</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel owner-panel">
                <div class="section-heading">
                    <span class="eyebrow">Complaints</span>
                    <h2>Жалобалар</h2>
                </div>
                <div class="owner-stack">
                    <?php foreach ($complaints as $complaint): ?>
                        <article class="owner-item">
                            <div class="project-meta">
                                <span><?= e($complaint['status']) ?></span>
                                <span>#<?= (int) $complaint['id'] ?></span>
                            </div>
                            <h3><?= e($complaint['subject']) ?></h3>
                            <p><?= e($complaint['body']) ?></p>
                            <p class="muted">Reporter: <?= e($complaint['reporter_name']) ?> · Reported: <?= e($complaint['reported_name'] ?? 'N/A') ?></p>
                            <form class="form mini js-ajax-form" action="ajax.php" method="post">
                                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                                <input type="hidden" name="action" value="owner_complaint_update">
                                <input type="hidden" name="complaint_id" value="<?= (int) $complaint['id'] ?>">
                                <select name="status">
                                    <?php foreach (['open', 'reviewing', 'resolved', 'dismissed'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $complaint['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <textarea name="owner_note" rows="2" placeholder="Owner note"><?= e($complaint['owner_note'] ?? '') ?></textarea>
                                <button class="btn btn-small" type="submit">Сақтау</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($complaints)): ?>
                        <p class="muted">Ашық жалоба жоқ.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="panel owner-panel owner-panel-wide">
                <div class="section-heading">
                    <span class="eyebrow">Projects</span>
                    <h2>Жобалар workflow</h2>
                </div>
                <div class="table-wrap">
                    <table class="owner-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Жоба</th>
                            <th>Автор / орындаушы</th>
                            <th>Бюджет</th>
                            <th>Статус</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?= (int) $project['id'] ?></td>
                                <td>
                                    <strong><?= e($project['title']) ?></strong>
                                    <span><?= e($project['category_name']) ?> · <?= (int) $project['proposals_count'] ?> отклик</span>
                                </td>
                                <td>
                                    <span><?= e($project['client_name']) ?></span>
                                    <span><?= e($project['assigned_name'] ?? 'Орындаушы жоқ') ?></span>
                                </td>
                                <td><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></td>
                                <td>
                                    <form class="owner-inline-form js-ajax-form" action="ajax.php" method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                                        <input type="hidden" name="action" value="owner_project_status">
                                        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                                        <select name="status">
                                            <?php foreach (['open', 'in_progress', 'submitted', 'completed', 'cancelled'] as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $project['status'] === $status ? 'selected' : '' ?>><?= e(project_status_label($status)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-small" type="submit">OK</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel owner-panel">
                <div class="section-heading">
                    <span class="eyebrow">Email</span>
                    <h2>Email log</h2>
                </div>
                <div class="mini-list">
                    <?php foreach ($emailLogs as $email): ?>
                        <div>
                            <strong><?= e($email['subject']) ?></strong>
                            <p><?= e($email['recipient_email']) ?></p>
                            <span><?= e($email['status']) ?> · <?= e($email['created_at']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($emailLogs)): ?>
                        <p class="muted">Email log әзірге бос.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="panel owner-panel">
                <div class="section-heading">
                    <span class="eyebrow">Audit</span>
                    <h2>Audit log</h2>
                </div>
                <div class="mini-list">
                    <?php foreach ($auditLogs as $audit): ?>
                        <div>
                            <strong><?= e($audit['action']) ?> · <?= e($audit['entity_type']) ?> #<?= e($audit['entity_id'] ?? '') ?></strong>
                            <p><?= e($audit['details'] ?? '') ?></p>
                            <span><?= e($audit['owner_name'] ?? 'Owner') ?> · <?= e($audit['created_at']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($auditLogs)): ?>
                        <p class="muted">Audit log әзірге бос.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </section>
<?php endif; ?>
</main>

<div class="toast" role="status" aria-live="polite"></div>
<script src="<?= e(asset('vendor/jquery-3.7.1.min.js')) ?>"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
