<?php
/**
 * Project: QazJumys
 * File: projects.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Displays searchable open projects and proposal forms.
 * RU: Показывает поиск открытых проектов и формы отклика.
 */

$selectedCategory = (int) ($_GET['category_id'] ?? 0);
$query = (string) ($_GET['q'] ?? '');
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Жобалар</span>
        <h1>Ашық digital тапсырмалар</h1>
        <p>Категория бойынша іздеңіз, талаптарды оқыңыз және орындаушы ретінде ұсыныс жіберіңіз.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar" action="index.php" method="get">
            <input type="hidden" name="page" value="projects">
            <label>
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
                            <span><?= e($project['client_name']) ?></span>
                            <span><?= (int) $project['proposals_count'] ?> ұсыныс</span>
                        </div>
                        <h2><?= e($project['title']) ?></h2>
                        <p><?= e($project['description']) ?></p>
                        <div class="project-footer">
                            <strong><?= number_format((float) $project['budget_min'], 0, '.', ' ') ?> - <?= number_format((float) $project['budget_max'], 0, '.', ' ') ?> ₸</strong>
                            <span><?= (int) $project['deadline_days'] ?> күн</span>
                        </div>
                    </div>
                    <aside class="proposal-box">
                        <?php if ($user && $user['role'] === 'freelancer'): ?>
                            <form class="form mini js-ajax-form" action="ajax.php" method="post">
                                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                <input type="hidden" name="action" value="proposal_create">
                                <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                                <label>
                                    <span>Ұсыныс</span>
                                    <textarea name="cover_letter" rows="4" required minlength="20" placeholder="Тәжірибеңізді және шешіміңізді қысқаша жазыңыз"></textarea>
                                </label>
                                <div class="two-cols">
                                    <label>
                                        <span>Баға, ₸</span>
                                        <input type="number" name="bid_amount" min="0" step="1000" required>
                                    </label>
                                    <label>
                                        <span>Күн</span>
                                        <input type="number" name="delivery_days" min="1" max="180" value="7" required>
                                    </label>
                                </div>
                                <button class="btn btn-primary btn-full" type="submit">Ұсыныс жіберу</button>
                            </form>
                        <?php elseif ($user && $user['role'] === 'client'): ?>
                            <p class="muted">Сіз тапсырыс беруші ретінде кірдіңіз.</p>
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
                <p>Іздеу сүзгісін өзгертіңіз немесе тапсырыс беруші ретінде алғашқы жобаны жариялаңыз.</p>
                <a class="btn btn-primary" href="<?= e(url_for('register')) ?>">Бастау</a>
            </div>
        <?php endif; ?>
    </div>
</section>
