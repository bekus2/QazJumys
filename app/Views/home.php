<?php
/**
 * Project: QazJumys
 * File: home.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Marketplace homepage for clients and freelancers in Kazakhstan.
 * RU: Главная страница маркетплейса для заказчиков и исполнителей Казахстана.
 */

$stats = $marketplaceStats ?? ['open_projects' => 0, 'proposals' => 0, 'freelancers' => 0, 'categories' => count($categories)];
$highlightedProjects = !empty($featuredProjects) ? $featuredProjects : $projects;
?>
<section class="hero">
    <div class="hero-media" aria-hidden="true">
        <img src="<?= e(asset('img/hero-marketplace.png')) ?>" alt="">
    </div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Қазақстандағы digital marketplace</span>
            <h1>QazJumys</h1>
            <p>Жоба жариялаңыз, нақты бюджет қойыңыз және SMM, сайт, жарнама, CRM, дизайн, видео бағытындағы орындаушылардан ұсыныс алыңыз.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e($user ? url_for('project-create') : url_for('register')) ?>">Жоба жариялау</a>
                <a class="btn btn-ghost" href="<?= e(url_for('projects')) ?>">Жұмыс табу</a>
            </div>
            <form class="market-search" action="index.php" method="get">
                <input type="hidden" name="page" value="projects">
                <label>
                    <span>Қандай жұмыс керек?</span>
                    <input type="search" name="q" placeholder="Сайт, CRM, Reels, Google Ads...">
                </label>
                <label>
                    <span>Категория</span>
                    <select name="category_id">
                        <option value="">Барлық бағыт</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Іздеу</button>
            </form>
        </div>
    </div>
</section>

<section class="market-strip" aria-label="QazJumys көрсеткіштері">
    <div class="container market-strip-grid">
        <div>
            <strong><?= (int) $stats['open_projects'] ?></strong>
            <span>ашық жоба</span>
        </div>
        <div>
            <strong><?= (int) $stats['freelancers'] ?></strong>
            <span>орындаушы профилі</span>
        </div>
        <div>
            <strong><?= (int) $stats['proposals'] ?></strong>
            <span>жіберілген ұсыныс</span>
        </div>
        <div>
            <strong><?= (int) $stats['categories'] ?></strong>
            <span>digital бағыт</span>
        </div>
    </div>
</section>

<section id="categories" class="section section-tight">
    <div class="container section-heading row-heading">
        <div>
            <span class="eyebrow">Қызмет бағыттары</span>
            <h2>Нақты мамандық бойынша тез іздеу</h2>
        </div>
        <a class="btn btn-small" href="<?= e(url_for('projects')) ?>">Барлық жобалар</a>
    </div>
    <div class="container category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-card" href="<?= e(url_for('projects', ['category_id' => $category['id']])) ?>" style="--accent: <?= e($category['accent_color'] ?? '#06b6d4') ?>">
                <span class="category-dot"></span>
                <h3><?= e($category['name']) ?></h3>
                <p><?= e($category['description']) ?></p>
                <span class="category-link">Жобаларды көру</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section band">
    <div class="container section-heading row-heading">
        <div>
            <span class="eyebrow">Таңдаулы тапсырмалар</span>
            <h2>Бюджеті, мерзімі және дағдылары көрсетілген жобалар</h2>
        </div>
        <a class="btn btn-small" href="<?= e(url_for('projects', ['sort' => 'budget_high'])) ?>">Жоғары бюджет</a>
    </div>
    <div class="container project-grid">
        <?php if (!empty($highlightedProjects)): ?>
            <?php foreach (array_slice($highlightedProjects, 0, 3) as $project): ?>
                <article class="project-card project-card-featured">
                    <div class="project-meta">
                        <span><?= e($project['category_name']) ?></span>
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
                    <div class="skill-row">
                        <?php foreach (skill_chips($project['skills'] ?? '', 3) as $skill): ?>
                            <span><?= e($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="project-footer">
                        <strong><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></strong>
                        <span><?= (int) $project['proposals_count'] ?> ұсыныс</span>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Жобаларға орын дайын</h3>
                <p>MySQL импортталғаннан кейін featured және соңғы жобалар осы жерде көрінеді.</p>
                <a class="btn btn-primary" href="<?= e($user ? url_for('project-create') : url_for('register')) ?>">Жоба жариялау</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section id="talent" class="section">
    <div class="container section-heading">
        <span class="eyebrow">Орындаушылар</span>
        <h2>Профиль, рейтинг, қала және дағды бір карточкада</h2>
    </div>
    <div class="container talent-grid">
        <?php if (!empty($topFreelancers)): ?>
            <?php foreach ($topFreelancers as $freelancer): ?>
                <article class="talent-card">
                    <div class="talent-top">
                        <div class="avatar small"><?= e(mb_strtoupper(mb_substr((string) $freelancer['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
                        <div>
                            <h3><?= e($freelancer['name']) ?></h3>
                            <p><?= e($freelancer['city'] ?: 'Қазақстан') ?></p>
                        </div>
                    </div>
                    <p class="talent-headline"><?= e($freelancer['headline'] ?? '') ?></p>
                    <div class="talent-stats">
                        <span>★ <?= number_format((float) $freelancer['rating'], 1) ?></span>
                        <span><?= (int) $freelancer['completed_projects'] ?> жұмыс</span>
                        <span><?= e($freelancer['response_time'] ?? 'Жауап береді') ?></span>
                    </div>
                    <div class="skill-row">
                        <?php foreach (skill_chips($freelancer['skills'] ?? '', 4) as $skill): ?>
                            <span><?= e($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($freelancer['hourly_rate'])): ?>
                        <strong class="rate"><?= e(format_money($freelancer['hourly_rate'])) ?> / сағ</strong>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Орындаушы профилдері дайындалады</h3>
                <p>Seed импортталғаннан кейін тексерілген мамандар осы жерде шығады.</p>
                <a class="btn btn-primary" href="<?= e(url_for('register')) ?>">Орындаушы болу</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section final-cta">
    <div class="container final-cta-grid">
        <div>
            <span class="eyebrow">Жұмысты бастау</span>
            <h2>Бір аккаунт арқылы жоба жариялап немесе ұсыныс жіберуге болады</h2>
        </div>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e(url_for('register')) ?>">Аккаунт ашу</a>
            <a class="btn btn-ghost" href="<?= e(url_for('projects')) ?>">Ашық жобалар</a>
        </div>
    </div>
</section>
