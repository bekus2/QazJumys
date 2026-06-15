<?php
/**
 * Project: QazJumys
 * File: home.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Homepage for the Kazakhstan freelance portal.
 * RU: Главная страница казахстанского фриланс-портала.
 */
?>
<section class="hero">
    <div class="hero-media" aria-hidden="true">
        <img src="<?= e(asset('img/hero-marketplace.png')) ?>" alt="">
    </div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Қазақстанға арналған digital freelance</span>
            <h1>Тапсырыс пен кәсіби орындаушы бір жерде</h1>
            <p>QazJumys SMM, жарнама, сайт, SEO, CRM, 1C, дизайн және видео бағытындағы мамандарды Қазақстан бизнесімен байланыстырады.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url_for('register')) ?>">Қазір бастау</a>
                <a class="btn btn-ghost" href="<?= e(url_for('projects')) ?>">Жобаларды көру</a>
            </div>
            <div class="hero-stats" aria-label="Портал көрсеткіштері">
                <div><strong>10</strong><span>бағыт</span></div>
                <div><strong>2</strong><span>рөл</span></div>
                <div><strong>24/7</strong><span>онлайн қолжетім</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container section-heading">
        <span class="eyebrow">Қызмет бағыттары</span>
        <h2>Қазақстан нарығына қажет негізгі digital мамандықтар</h2>
    </div>
    <div class="container category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-card" href="<?= e(url_for('projects', ['category_id' => $category['id']])) ?>" style="--accent: <?= e($category['accent_color'] ?? '#06b6d4') ?>">
                <span class="category-dot"></span>
                <h3><?= e($category['name']) ?></h3>
                <p><?= e($category['description']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section band">
    <div class="container split">
        <div>
            <span class="eyebrow">Жұмыс логикасы</span>
            <h2>Тапсырыс беруші жоба ашады, орындаушы нақты ұсыныс жібереді</h2>
        </div>
        <div class="steps">
            <div class="step">
                <span>01</span>
                <h3>Жоба жариялау</h3>
                <p>Категория, бюджет, мерзім және міндетті сипаттама енгізіледі.</p>
            </div>
            <div class="step">
                <span>02</span>
                <h3>Ұсыныстар алу</h3>
                <p>Орындаушылар баға, мерзім және жұмыс тәсілін көрсетеді.</p>
            </div>
            <div class="step">
                <span>03</span>
                <h3>Кабинетте бақылау</h3>
                <p>Әр рөл өзінің жобаларын, ұсыныстарын және профилін басқарады.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container section-heading row-heading">
        <div>
            <span class="eyebrow">Ашық жобалар</span>
            <h2>Соңғы жарияланған тапсырмалар</h2>
        </div>
        <a class="btn btn-small" href="<?= e(url_for('projects')) ?>">Барлығын көру</a>
    </div>
    <div class="container project-grid">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <article class="project-card">
                    <div class="project-meta">
                        <span><?= e($project['category_name']) ?></span>
                        <span><?= (int) $project['proposals_count'] ?> ұсыныс</span>
                    </div>
                    <h3><?= e($project['title']) ?></h3>
                    <p><?= e(mb_substr((string) $project['description'], 0, 150, 'UTF-8')) ?>...</p>
                    <div class="project-footer">
                        <strong><?= number_format((float) $project['budget_min'], 0, '.', ' ') ?> - <?= number_format((float) $project['budget_max'], 0, '.', ' ') ?> ₸</strong>
                        <span><?= (int) $project['deadline_days'] ?> күн</span>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Алғашқы жобаларға дайын кеңістік</h3>
                <p>MySQL бапталғаннан кейін тапсырыс берушілер жобаларын осы жерден көре алады.</p>
                <a class="btn btn-primary" href="<?= e(url_for('register')) ?>">Аккаунт ашу</a>
            </div>
        <?php endif; ?>
    </div>
</section>
