<?php
/**
 * Project: QazJumys
 * File: projects.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Displays searchable open projects and secure proposal forms.
 * RU: Показывает поиск открытых проектов и защищенные формы отклика.
 */

$filters = $projectFilters ?? [];
$selectedCategory = (int) ($filters['category_id'] ?? 0);
$query = (string) ($filters['q'] ?? '');
$projectType = (string) ($filters['project_type'] ?? '');
$experienceLevel = (string) ($filters['experience_level'] ?? '');
$sort = (string) ($filters['sort'] ?? 'latest');
$budgetMin = $filters['budget_min'] ?? null;
$budgetMax = $filters['budget_max'] ?? null;
?>
<section class="page-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow">Жобалар</span>
            <h1>Ашық digital тапсырмалар</h1>
            <p>Категория, бюджет, дағды және мерзім бойынша таңдаңыз. Орындаушы аккаунты ұсыныс жібере алады.</p>
        </div>
        <div class="page-stat">
            <strong><?= count($projects) ?></strong>
            <span>нәтиже</span>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar filter-bar-advanced" action="index.php" method="get">
            <input type="hidden" name="page" value="projects">
            <label class="filter-wide">
                <span>Іздеу</span>
                <input type="search" name="q" value="<?= e($query) ?>" placeholder="Мысалы: сайт, reels, CRM">
            </label>
            <label>
                <span>Категория</span>
                <select name="category_id">
                    <option value="">Барлығы</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= $selectedCategory === (int) $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Тип</span>
                <select name="project_type">
                    <option value="">Барлығы</option>
                    <option value="fixed" <?= $projectType === 'fixed' ? 'selected' : '' ?>>Бір жолғы</option>
                    <option value="hourly" <?= $projectType === 'hourly' ? 'selected' : '' ?>>Сағаттық</option>
                </select>
            </label>
            <label>
                <span>Деңгей</span>
                <select name="experience_level">
                    <option value="">Барлығы</option>
                    <option value="entry" <?= $experienceLevel === 'entry' ? 'selected' : '' ?>>Junior</option>
                    <option value="intermediate" <?= $experienceLevel === 'intermediate' ? 'selected' : '' ?>>Middle</option>
                    <option value="expert" <?= $experienceLevel === 'expert' ? 'selected' : '' ?>>Expert</option>
                </select>
            </label>
            <label>
                <span>Бюджеттен, ₸</span>
                <input type="number" name="budget_min" min="0" step="1000" value="<?= e($budgetMin ?? '') ?>">
            </label>
            <label>
                <span>Бюджетке дейін, ₸</span>
                <input type="number" name="budget_max" min="0" step="1000" value="<?= e($budgetMax ?? '') ?>">
            </label>
            <label>
                <span>Сорттау</span>
                <select name="sort">
                    <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Ұсынылған</option>
                    <option value="budget_high" <?= $sort === 'budget_high' ? 'selected' : '' ?>>Бюджет жоғары</option>
                    <option value="budget_low" <?= $sort === 'budget_low' ? 'selected' : '' ?>>Бюджет төмен</option>
                    <option value="deadline_soon" <?= $sort === 'deadline_soon' ? 'selected' : '' ?>>Мерзімі жақын</option>
                    <option value="proposals_low" <?= $sort === 'proposals_low' ? 'selected' : '' ?>>Отклик аз</option>
                </select>
            </label>
            <button class="btn btn-primary" type="submit">Табу</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container project-list">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <article class="project-row">
                    <div class="project-main">
                        <div class="project-meta">
                            <span><?= e($project['category_name']) ?></span>
                            <span><?= e(project_type_label((string) $project['project_type'])) ?></span>
                            <span><?= e(experience_level_label((string) $project['experience_level'])) ?></span>
                            <?php if (!empty($project['is_featured'])): ?>
                                <span class="badge-featured">Таңдаулы</span>
                            <?php endif; ?>
                            <?php if (!empty($project['is_urgent'])): ?>
                                <span class="badge-hot">Жедел</span>
                            <?php endif; ?>
                        </div>
                        <h2><?= e($project['title']) ?></h2>
                        <p><?= e($project['description']) ?></p>
                        <div class="skill-row">
                            <?php foreach (skill_chips($project['skills'] ?? '', 6) as $skill): ?>
                                <span><?= e($skill) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="project-footer">
                            <strong><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></strong>
                            <span><?= (int) $project['deadline_days'] ?> күн</span>
                            <span><?= e($project['location'] ?: ($project['client_city'] ?? 'Қазақстан')) ?></span>
                            <span><?= (int) $project['proposals_count'] ?> ұсыныс</span>
                        </div>
                    </div>
                    <aside class="proposal-box">
                        <div class="client-line">
                            <span>Тапсырыс беруші</span>
                            <strong><?= e($project['client_name']) ?></strong>
                        </div>
                        <?php if ($user && $user['role'] === 'freelancer'): ?>
                            <form class="form mini js-ajax-form" action="ajax.php" method="post">
                                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                <input type="hidden" name="action" value="proposal_create">
                                <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                                <label>
                                    <span>Ұсыныс</span>
                                    <textarea name="cover_letter" rows="4" required minlength="20" placeholder="Тәжірибеңізді, шешіміңізді және бірінші қадамды жазыңыз"></textarea>
                                </label>
                                <div class="two-cols">
                                    <label>
                                        <span>Баға, ₸</span>
                                        <input type="number" name="bid_amount" min="0" step="1000" required>
                                    </label>
                                    <label>
                                        <span>Күн</span>
                                        <input type="number" name="delivery_days" min="1" max="180" value="<?= (int) min(30, max(1, (int) $project['deadline_days'])) ?>" required>
                                    </label>
                                </div>
                                <button class="btn btn-primary btn-full" type="submit">Ұсыныс жіберу</button>
                            </form>
                        <?php elseif ($user && $user['role'] === 'client'): ?>
                            <p class="muted">Сіз тапсырыс беруші аккаунтымен кірдіңіз.</p>
                            <a class="btn btn-small" href="<?= e(url_for('project-create')) ?>">Жаңа жоба</a>
                        <?php else: ?>
                            <p class="muted">Ұсыныс жіберу үшін орындаушы аккаунты қажет.</p>
                            <a class="btn btn-small" href="<?= e(url_for('register')) ?>">Тіркелу</a>
                        <?php endif; ?>
                    </aside>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>Әзірге жоба табылмады</h2>
                <p>Сүзгіні өзгертіңіз немесе тапсырыс беруші ретінде алғашқы жобаны жариялаңыз.</p>
                <a class="btn btn-primary" href="<?= e($user ? url_for('project-create') : url_for('register')) ?>">Жоба жариялау</a>
            </div>
        <?php endif; ?>
    </div>
</section>
