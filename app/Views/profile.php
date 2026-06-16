<?php
/**
 * Project: QazJumys
 * File: profile.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Profile editing page for authenticated users.
 * RU: Страница редактирования профиля авторизованного пользователя.
 */

$isFreelancer = ($profile['role'] ?? '') === 'freelancer';
$headline = (string) ($profile['headline'] ?? '');
?>
<section class="page-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow">Профиль</span>
            <h1>Нарықтағы карточкаңызды жаңартыңыз</h1>
            <p>Аты-жөні, қала, қысқа позиция, дағдылар және тәжірибе сапалы ұсыныс алуға көмектеседі.</p>
        </div>
        <div class="page-stat">
            <strong><?= e($isFreelancer ? '★ ' . number_format((float) ($profile['rating'] ?? 0), 1) : 'B2B') ?></strong>
            <span><?= e($isFreelancer ? 'рейтинг' : 'профиль') ?></span>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container profile-grid">
        <form class="panel form js-ajax-form" action="ajax.php" method="post">
            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
            <input type="hidden" name="action" value="profile_update">
            <div class="two-cols">
                <label>
                    <span>Аты-жөні</span>
                    <input type="text" name="name" value="<?= e($profile['name'] ?? '') ?>" required maxlength="120">
                </label>
                <label>
                    <span>Қала</span>
                    <input type="text" name="city" value="<?= e($profile['city'] ?? '') ?>" maxlength="80">
                </label>
            </div>
            <label>
                <span>Қысқа позиция</span>
                <input type="text" name="headline" value="<?= e($profile['headline'] ?? '') ?>" maxlength="160" placeholder="SMM стратегия, CRM автоматтандыру немесе бизнес иесі">
            </label>
            <?php if ($isFreelancer): ?>
                <label>
                    <span>Сағаттық ставка, ₸</span>
                    <input type="number" name="hourly_rate" value="<?= e($profile['hourly_rate'] ?? '') ?>" min="0" step="1000">
                </label>
            <?php else: ?>
                <input type="hidden" name="hourly_rate" value="">
            <?php endif; ?>
            <label>
                <span>Қысқаша био</span>
                <textarea name="bio" rows="5" maxlength="1200" placeholder="Тәжірибеңіз, жұмыс тәсілі және нәтижелеріңіз"><?= e($profile['bio'] ?? '') ?></textarea>
            </label>
            <label>
                <span>Дағдылар / бағыттар</span>
                <textarea name="skills" rows="4" maxlength="600" placeholder="SMM, Google Ads, React, CRM, 1C..."><?= e($profile['skills'] ?? '') ?></textarea>
            </label>
            <button class="btn btn-primary btn-full" type="submit">Сақтау</button>
        </form>
        <aside class="profile-card">
            <div class="avatar"><?= e(mb_strtoupper(mb_substr((string) ($profile['name'] ?? 'Q'), 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
            <h2><?= e($profile['name'] ?? '') ?></h2>
            <p><?= e($headline !== '' ? $headline : (($profile['role'] ?? '') === 'client' ? 'Тапсырыс беруші' : 'Орындаушы')) ?></p>
            <div class="profile-metrics">
                <?php if ($isFreelancer): ?>
                    <span>★ <?= number_format((float) ($profile['rating'] ?? 0), 1) ?></span>
                    <span><?= (int) ($profile['completed_projects'] ?? 0) ?> жұмыс</span>
                    <span><?= (int) ($profile['reviews_count'] ?? 0) ?> пікір</span>
                <?php else: ?>
                    <span>Client</span>
                    <span><?= e($profile['city'] ?: 'Қазақстан') ?></span>
                <?php endif; ?>
            </div>
            <div class="skill-row centered">
                <?php foreach (skill_chips($profile['skills'] ?? '', 6) as $skill): ?>
                    <span><?= e($skill) ?></span>
                <?php endforeach; ?>
            </div>
            <dl>
                <div>
                    <dt>Email</dt>
                    <dd><?= e($profile['email'] ?? '') ?></dd>
                </div>
                <div>
                    <dt>Қала</dt>
                    <dd><?= e($profile['city'] ?? 'Көрсетілмеген') ?></dd>
                </div>
                <?php if ($isFreelancer && !empty($profile['hourly_rate'])): ?>
                    <div>
                        <dt>Ставка</dt>
                        <dd><?= e(format_money($profile['hourly_rate'])) ?> / сағ</dd>
                    </div>
                <?php endif; ?>
            </dl>
        </aside>
    </div>
</section>
