<?php
/**
 * Project: QazJumys
 * File: dashboard.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Role-based private dashboard with marketplace activity.
 * RU: Личный кабинет по ролям с активностью маркетплейса.
 */

$roleLabel = ($user['role'] ?? '') === 'client' ? 'Тапсырыс беруші' : 'Орындаушы';
?>
<section class="page-hero">
    <div class="container dashboard-head">
        <div>
            <span class="eyebrow"><?= e($roleLabel) ?></span>
            <h1>Сәлем, <?= e($profile['name'] ?? $user['name'] ?? '') ?></h1>
            <p>Кабинетте жобалар, ұсыныстар және профиль сапасы бір жерден бақыланады.</p>
        </div>
        <div class="hero-actions">
            <a class="btn btn-ghost" href="<?= e(url_for('profile')) ?>">Профильді өңдеу</a>
            <?php if ($user['role'] === 'client'): ?>
                <a class="btn btn-primary" href="<?= e(url_for('project-create')) ?>">Жоба жариялау</a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?= e(url_for('projects')) ?>">Жұмыс табу</a>
            <?php endif; ?>
        </div>
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
            <a class="btn btn-primary" href="<?= e(url_for('project-create')) ?>">Жаңа жоба</a>
        </div>
        <div class="container project-grid">
            <?php if (!empty($myProjects)): ?>
                <?php foreach ($myProjects as $project): ?>
                    <article class="project-card">
                        <div class="project-meta">
                            <span><?= e($project['category_name']) ?></span>
                            <span><?= e($project['status']) ?></span>
                            <?php if (!empty($project['is_urgent'])): ?>
                                <span class="badge-hot">Жедел</span>
                            <?php endif; ?>
                        </div>
                        <h3><?= e($project['title']) ?></h3>
                        <p><?= e(mb_substr((string) $project['description'], 0, 150, 'UTF-8')) ?>...</p>
                        <div class="chip-row">
                            <span><?= e(project_type_label((string) $project['project_type'])) ?></span>
                            <span><?= e(experience_level_label((string) $project['experience_level'])) ?></span>
                            <span><?= (int) $project['deadline_days'] ?> күн</span>
                        </div>
                        <div class="project-footer">
                            <strong><?= (int) $project['proposals_count'] ?> ұсыныс</strong>
                            <span><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></span>
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
                                <span><?= e(project_type_label((string) $proposal['project_type'])) ?></span>
                            </div>
                            <h2><?= e($proposal['project_title']) ?></h2>
                            <p><?= e(mb_substr((string) $proposal['cover_letter'], 0, 180, 'UTF-8')) ?>...</p>
                            <div class="chip-row">
                                <span><?= e($proposal['client_name']) ?></span>
                                <span><?= e(format_money($proposal['budget_min'])) ?> - <?= e(format_money($proposal['budget_max'])) ?></span>
                            </div>
                        </div>
                        <div class="proposal-summary">
                            <strong><?= e(format_money($proposal['bid_amount'])) ?></strong>
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

    <section class="section band">
        <div class="container section-heading">
            <span class="eyebrow">Жаңа мүмкіндіктер</span>
            <h2>Соңғы ашық жобалар</h2>
        </div>
        <div class="container project-grid">
            <?php foreach ($recommendedProjects as $project): ?>
                <article class="project-card">
                    <div class="project-meta">
                        <span><?= e($project['category_name']) ?></span>
                        <span><?= (int) $project['proposals_count'] ?> ұсыныс</span>
                    </div>
                    <h3><?= e($project['title']) ?></h3>
                    <p><?= e(mb_substr((string) $project['description'], 0, 130, 'UTF-8')) ?>...</p>
                    <div class="project-footer">
                        <strong><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></strong>
                        <a class="btn btn-small" href="<?= e(url_for('projects', ['q' => $project['title']])) ?>">Қарау</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
