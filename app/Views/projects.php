<?php
/**
 * Project: QazJumys
 * File: projects.php
 * Author: Beck Sarbassov
 * Version: 1.5.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-07-05
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Displays searchable open projects with pagination, saved projects, saved searches, project metrics, and secure proposal forms.
 * RU: Показывает поиск открытых проектов с пагинацией, сохранением проектов, сохранением поиска, метриками и защищенными формами отклика.
 */

$filters = $projectFilters ?? [];
$selectedCategory = (int) ($filters['category_id'] ?? 0);
$query = (string) ($filters['q'] ?? '');
$projectType = (string) ($filters['project_type'] ?? '');
$experienceLevel = (string) ($filters['experience_level'] ?? '');
$sort = (string) ($filters['sort'] ?? 'latest');
$budgetMin = $filters['budget_min'] ?? null;
$budgetMax = $filters['budget_max'] ?? null;
$isRemote = !empty($filters['is_remote']);
$isUrgent = !empty($filters['is_urgent']);
$verifiedClient = !empty($filters['verified_client']);
$savedIds = array_map('intval', $savedProjectIds ?? []);
$currentQueryString = $_SERVER['QUERY_STRING'] ?? 'page=projects';
$resultsTotal = (int) ($projectsTotal ?? count($projects));
$currentListPage = max(1, (int) ($projectsPage ?? 1));
$perPage = max(1, (int) ($projectsPerPage ?? 20));
$totalPages = max(1, (int) ceil($resultsTotal / $perPage));

/**
 * EN: Builds a projects page URL preserving current filters.
 * RU: Формирует URL страницы проектов с сохранением текущих фильтров.
 *
 * @param int $pageNumber Target page number / Номер целевой страницы
 * @return string
 */
$listPageUrl = static function (int $pageNumber): string {
    $params = $_GET;
    $params['page'] = 'projects';
    $params['pg'] = $pageNumber;

    return 'index.php?' . http_build_query($params);
};
?>
<section class="page-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow">Жобалар</span>
            <h1>Ашық digital тапсырмалар</h1>
            <p>Категория, бюджет, дағды, мерзім және сенім белгілері бойынша таңдаңыз. Бір аккаунтпен жоба жариялап та, басқа жобаларға ұсыныс жіберіп те жұмыс істей аласыз.</p>
        </div>
        <div class="page-stat">
            <strong><?= $resultsTotal ?></strong>
            <span>нәтиже</span>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar filter-bar-advanced marketplace-filter" action="index.php" method="get">
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
                    <option value="activity" <?= $sort === 'activity' ? 'selected' : '' ?>>Белсенді</option>
                    <option value="views_high" <?= $sort === 'views_high' ? 'selected' : '' ?>>Көп қаралған</option>
                    <option value="budget_high" <?= $sort === 'budget_high' ? 'selected' : '' ?>>Бюджет жоғары</option>
                    <option value="budget_low" <?= $sort === 'budget_low' ? 'selected' : '' ?>>Бюджет төмен</option>
                    <option value="deadline_soon" <?= $sort === 'deadline_soon' ? 'selected' : '' ?>>Мерзімі жақын</option>
                    <option value="proposals_low" <?= $sort === 'proposals_low' ? 'selected' : '' ?>>Отклик аз</option>
                </select>
            </label>
            <div class="filter-switches">
                <label class="check-card">
                    <input type="checkbox" name="is_remote" value="1" <?= $isRemote ? 'checked' : '' ?>>
                    <span>Remote</span>
                </label>
                <label class="check-card">
                    <input type="checkbox" name="is_urgent" value="1" <?= $isUrgent ? 'checked' : '' ?>>
                    <span>Жедел</span>
                </label>
                <label class="check-card">
                    <input type="checkbox" name="verified_client" value="1" <?= $verifiedClient ? 'checked' : '' ?>>
                    <span>Verified client</span>
                </label>
            </div>
            <button class="btn btn-primary" type="submit">Табу</button>
        </form>

        <?php if ($user && ($user['role'] ?? '') !== 'owner'): ?>
            <form class="saved-search-bar js-ajax-form" action="ajax.php" method="post">
                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                <input type="hidden" name="action" value="saved_search_create">
                <input type="hidden" name="query_string" value="<?= e($currentQueryString) ?>">
                <label>
                    <span>Іздеуді сақтау</span>
                    <input type="text" name="label" maxlength="120" placeholder="Мысалы: CRM жобалары, remote, middle">
                </label>
                <button class="btn btn-small" type="submit">Сақтау</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container project-list">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <?php $projectId = (int) $project['id']; ?>
                <article class="project-row enhanced-project-row">
                    <div class="project-main">
                        <div class="project-meta">
                            <span><?= e($project['category_name']) ?></span>
                            <span><?= e(project_type_label((string) $project['project_type'])) ?></span>
                            <span><?= e(experience_level_label((string) $project['experience_level'])) ?></span>
                            <?php if (!empty($project['client_is_verified'])): ?>
                                <span class="badge-trust">Verified client</span>
                            <?php endif; ?>
                            <?php if (!empty($project['is_remote'])): ?>
                                <span>Remote</span>
                            <?php endif; ?>
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
                        <div class="project-footer project-metrics-line">
                            <strong><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></strong>
                            <span><?= (int) $project['deadline_days'] ?> күн</span>
                            <span><?= e($project['location'] ?: ($project['client_city'] ?? 'Қазақстан')) ?></span>
                            <span><?= (int) $project['proposals_count'] ?> ұсыныс</span>
                            <span><?= (int) ($project['views_count'] ?? 0) ?> views</span>
                            <span><?= (int) ($project['saved_count'] ?? 0) ?> saved</span>
                        </div>
                    </div>
                    <aside class="proposal-box">
                        <div class="client-line">
                            <span>Тапсырыс беруші</span>
                            <strong><?= e($project['client_name']) ?></strong>
                        </div>
                        <?php if ($user && ($user['role'] ?? '') !== 'owner'): ?>
                            <form class="save-project-form js-ajax-form" action="ajax.php" method="post">
                                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                <input type="hidden" name="action" value="project_save_toggle">
                                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                                <button class="btn btn-small btn-ghost" type="submit"><?= in_array($projectId, $savedIds, true) ? 'Сақталған' : 'Сақтау' ?></button>
                            </form>
                        <?php endif; ?>
                        <?php if ($user && ($user['role'] ?? '') !== 'owner' && (int) $user['id'] !== (int) $project['client_id']): ?>
                            <form class="form mini js-ajax-form" action="ajax.php" method="post">
                                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                <input type="hidden" name="action" value="proposal_create">
                                <input type="hidden" name="project_id" value="<?= $projectId ?>">
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
                        <?php elseif ($user && (int) $user['id'] === (int) $project['client_id']): ?>
                            <p class="muted">Бұл сіздің жобаңыз. Келген откликтер кабинетте басқарылады.</p>
                            <a class="btn btn-small" href="<?= e(url_for('dashboard')) ?>">Кабинет</a>
                        <?php elseif ($user && ($user['role'] ?? '') === 'owner'): ?>
                            <p class="muted">Owner аккаунты басқаруға арналған.</p>
                            <a class="btn btn-small" href="owner.php">Owner panel</a>
                        <?php else: ?>
                            <p class="muted">Ұсыныс жіберу үшін аккаунт ашыңыз.</p>
                            <a class="btn btn-small" href="<?= e(url_for('register')) ?>">Тіркелу</a>
                        <?php endif; ?>
                    </aside>
                </article>
            <?php endforeach; ?>
            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Беттер">
                    <?php if ($currentListPage > 1): ?>
                        <a class="btn btn-small btn-ghost" href="<?= e($listPageUrl($currentListPage - 1)) ?>">&larr; Алдыңғы</a>
                    <?php endif; ?>
                    <span class="muted"><?= $currentListPage ?> / <?= $totalPages ?> бет</span>
                    <?php if ($currentListPage < $totalPages): ?>
                        <a class="btn btn-small btn-ghost" href="<?= e($listPageUrl($currentListPage + 1)) ?>">Келесі &rarr;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>Әзірге жоба табылмады</h2>
                <p>Сүзгіні өзгертіңіз немесе тапсырыс беруші ретінде алғашқы жобаны жариялаңыз.</p>
                <a class="btn btn-primary" href="<?= e($user ? url_for('project-create') : url_for('register')) ?>">Жоба жариялау</a>
            </div>
        <?php endif; ?>
    </div>
</section>
