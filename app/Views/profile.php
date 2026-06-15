<?php
/**
 * Project: QazJumys
 * File: profile.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Profile editing page for authenticated users.
 * RU: Страница редактирования профиля авторизованного пользователя.
 */
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Профиль</span>
        <h1>Ақпаратыңызды жаңартыңыз</h1>
        <p>Қала, тәжірибе және дағдылар тапсырыс пен ұсыныс сапасын жақсартады.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container profile-grid">
        <form class="panel form js-ajax-form" action="ajax.php" method="post">
            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
            <input type="hidden" name="action" value="profile_update">
            <label>
                <span>Аты-жөні</span>
                <input type="text" name="name" value="<?= e($profile['name'] ?? '') ?>" required maxlength="120">
            </label>
            <label>
                <span>Қала</span>
                <input type="text" name="city" value="<?= e($profile['city'] ?? '') ?>" maxlength="80">
            </label>
            <label>
                <span>Қысқаша био</span>
                <textarea name="bio" rows="5" maxlength="1200" placeholder="Тәжірибеңіз, жұмыс тәсілі және нәтижелеріңіз"><?= e($profile['bio'] ?? '') ?></textarea>
            </label>
            <label>
                <span>Дағдылар</span>
                <textarea name="skills" rows="4" maxlength="600" placeholder="SMM, Google Ads, React, CRM, 1C..."><?= e($profile['skills'] ?? '') ?></textarea>
            </label>
            <button class="btn btn-primary btn-full" type="submit">Сақтау</button>
        </form>
        <aside class="profile-card">
            <div class="avatar"><?= e(mb_strtoupper(mb_substr((string) ($profile['name'] ?? 'Q'), 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
            <h2><?= e($profile['name'] ?? '') ?></h2>
            <p><?= e(($profile['role'] ?? '') === 'client' ? 'Тапсырыс беруші' : 'Орындаушы') ?></p>
            <dl>
                <div>
                    <dt>Email</dt>
                    <dd><?= e($profile['email'] ?? '') ?></dd>
                </div>
                <div>
                    <dt>Қала</dt>
                    <dd><?= e($profile['city'] ?? 'Көрсетілмеген') ?></dd>
                </div>
            </dl>
        </aside>
    </div>
</section>
