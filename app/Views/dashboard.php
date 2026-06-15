<?php
/**
 * Project: QazJumys
 * File: dashboard.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Role-based private dashboard.
 * RU: Личный кабинет с логикой по ролям.
 */

$roleLabel = ($user['role'] ?? '') === 'client' ? 'Тапсырыс беруші' : 'Орындаушы';
?>
<section class="page-hero">
    <div class="container dashboard-head">
        <div>
            <span class="eyebrow"><?= e($roleLabel) ?></span>
            <h1>Сәлем, <?= e($profile['name'] ?? $user['name'] ?? '') ?></h1>
            <p>Кабинетте рөліңізге байланысты жобалар мен ұсыныстар көрсетіледі.</p>
        </div>
        <a class="btn btn-ghost" href="<?= e(url_for('profile')) ?>">Профильді өңдеу</a>
    </div>
</section>

<section class="section section-tight">
    <div class="container stat-grid">
        <div class="stat-card">
            <span><?= $user['role'] === 'client' ? 'Менің жобаларым' : 'Ашық жобалар' ?></span>
            <strong><?= (int) $dashboardStats['projects'] ?></strong>
        </div>
        <div class="stat-card">
            <span><?= $user['role'] === 'client' ? 'Келген ұсыныстар' : 'Менің ұсыныстарым' ?></span>
            <strong><?= (int) $dashboardStats['proposals'] ?></strong>
        </div>
        <div class="stat-card">
            <span>Нарықтағы ашық жобалар</span>
            <strong><?= (int) $dashboardStats['open_market'] ?></strong>
        </div>
    </div>
</section>

<?php if ($user['role'] === 'client'): ?>
    <section class="section">
        <div class="container section-heading row-heading">
            <div>
                <span class="eyebrow">Жобаларым</span>
                <h2>Жарияланған тапсырмалар</h2>
            </div>
            <a class="btn btn-primary" href="<?= e(url_for('project-create')) ?>">Жоба жариялау</a>
        </div>
        <div class="container project-grid">
            <?php if (!empty($myProjects)): ?>
                <?php foreach ($myProjects as $project): ?>
                    <article class="project-card">
                        <div class="project-meta">
                            <span><?= e($project['category_name']) ?></span>
                            <span><?= e($project['status']) ?></span>
                        </div>
                        <h3><?= e($project['title']) ?></h3>
                        <p><?= e(mb_substr((string) $project['description'], 0, 150, 'UTF-8')) ?>...</p>
                        <div class="project-footer">
                            <strong><?= (int) $project['proposals_count'] ?> ұсыныс</strong>
                            <span><?= number_format((float) $project['budget_min'], 0, '.', ' ') ?> - <?= number_format((float) $project['budget_max'], 0, '.', ' ') ?> ₸</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Әлі жоба жарияланбаған</h3>
                    <p>Алғашқы тапсырманы жариялап, орындаушылардан ұсыныс алыңыз.</p>
                    <a class="btn btn-primary" href="<?= e(url_for('project-create')) ?>">Жоба жариялау</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="section">
        <div class="container section-heading row-heading">
            <div>
                <span class="eyebrow">Ұсыныстарым</span>
                <h2>Жіберілген откликтер</h2>
            </div>
            <a class="btn btn-primary" href="<?= e(url_for('projects')) ?>">Жобаларды қарау</a>
        </div>
        <div class="container project-list">
            <?php if (!empty($myProposals)): ?>
                <?php foreach ($myProposals as $proposal): ?>
                    <article class="project-row slim">
                        <div>
                            <div class="project-meta">
                                <span><?= e($proposal['category_name']) ?></span>
                                <span><?= e($proposal['status']) ?></span>
                                <span><?= e($proposal['project_status']) ?></span>
                            </div>
                            <h2><?= e($proposal['project_title']) ?></h2>
                            <p><?= e(mb_substr((string) $proposal['cover_letter'], 0, 180, 'UTF-8')) ?>...</p>
                        </div>
                        <div class="proposal-summary">
                            <strong><?= number_format((float) $proposal['bid_amount'], 0, '.', ' ') ?> ₸</strong>
                            <span><?= (int) $proposal['delivery_days'] ?> күн</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Ұсыныс әлі жіберілмеген</h3>
                    <p>Ашық жобаларды қарап, өз тәжірибеңізге сәйкес ұсыныс беріңіз.</p>
                    <a class="btn btn-primary" href="<?= e(url_for('projects')) ?>">Жобаларды қарау</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
