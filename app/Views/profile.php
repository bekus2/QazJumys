<?php
/**
 * Project: QazJumys
 * File: profile.php
 * Author: Beck Sarbassov
 * Version: 1.3.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-21
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Profile editing page with portfolio, reviews, verification request, and password change tools.
 * RU: Страница редактирования профиля с портфолио, отзывами, заявкой на верификацию и сменой пароля.
 */

$headline = (string) ($profile['headline'] ?? '');
$verificationStatus = (string) ($verificationRequest['status'] ?? '');
?>
<section class="page-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow">Профиль</span>
            <h1>Нарықтағы карточкаңызды жаңартыңыз</h1>
            <p>Аты-жөні, қала, позиция, дағдылар, портфолио және сенім белгілері сапалы ұсыныс алуға және жақсы жобаларға өтуге көмектеседі.</p>
        </div>
        <div class="page-stat">
            <strong>★ <?= number_format((float) ($profile['rating'] ?? 0), 1) ?></strong>
            <span><?= !empty($profile['is_verified']) ? 'verified' : 'profile' ?></span>
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
            <label>
                <span>Сағаттық ставка, ₸</span>
                <input type="number" name="hourly_rate" value="<?= e($profile['hourly_rate'] ?? '') ?>" min="0" step="1000" placeholder="Егер орындаушы ретінде жұмыс алсаңыз">
            </label>
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
            <p><?= e($headline !== '' ? $headline : 'Маркетплейс қатысушысы') ?></p>
            <div class="profile-metrics">
                <span>★ <?= number_format((float) ($profile['rating'] ?? 0), 1) ?></span>
                <span><?= (int) ($profile['completed_projects'] ?? 0) ?> жұмыс</span>
                <span><?= e($profile['city'] ?: 'Қазақстан') ?></span>
                <?php if (!empty($profile['is_verified'])): ?>
                    <span>Verified</span>
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
                <?php if (!empty($profile['hourly_rate'])): ?>
                    <div>
                        <dt>Ставка</dt>
                        <dd><?= e(format_money($profile['hourly_rate'])) ?> / сағ</dd>
                    </div>
                <?php endif; ?>
            </dl>
        </aside>
    </div>
</section>

<section class="section band">
    <div class="container profile-tools-grid">
        <section class="panel">
            <div class="section-heading">
                <span class="eyebrow">Security</span>
                <h2>Парольді өзгерту</h2>
            </div>
            <form class="form mini js-ajax-form" action="ajax.php" method="post">
                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                <input type="hidden" name="action" value="password_change">
                <label>
                    <span>Қазіргі пароль</span>
                    <input type="password" name="current_password" required autocomplete="current-password">
                </label>
                <label>
                    <span>Жаңа пароль</span>
                    <input type="password" name="new_password" required minlength="10" autocomplete="new-password">
                </label>
                <button class="btn btn-primary btn-full" type="submit">Жаңарту</button>
            </form>
        </section>

        <section class="panel">
            <div class="section-heading">
                <span class="eyebrow">Verification</span>
                <h2>Верификация</h2>
            </div>
            <?php if (!empty($profile['is_verified'])): ?>
                <p class="muted">Профиль verified белгісімен расталған.</p>
            <?php elseif ($verificationStatus === 'pending'): ?>
                <p class="muted">Заявка owner қарауында. Жіберілген уақыт: <?= e($verificationRequest['created_at'] ?? '') ?></p>
            <?php else: ?>
                <form class="form mini js-ajax-form" action="ajax.php" method="post">
                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                    <input type="hidden" name="action" value="verification_request">
                    <label>
                        <span>Дәлелдеме</span>
                        <textarea name="note" rows="5" minlength="20" maxlength="1200" placeholder="Тәжірибе, компания, сайт, портфолио немесе құжаттық дәлел туралы қысқаша жазыңыз"></textarea>
                    </label>
                    <button class="btn btn-primary btn-full" type="submit">Заявка жіберу</button>
                </form>
            <?php endif; ?>
            <?php if (!empty($verificationRequest['owner_note'])): ?>
                <p class="muted">Owner note: <?= e($verificationRequest['owner_note']) ?></p>
            <?php endif; ?>
        </section>
    </div>
</section>

<section class="section">
    <div class="container profile-tools-grid">
        <section class="panel">
            <div class="section-heading">
                <span class="eyebrow">Portfolio</span>
                <h2>Жұмыс үлгілері</h2>
            </div>
            <form class="form mini js-ajax-form" action="ajax.php" method="post">
                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                <input type="hidden" name="action" value="portfolio_create">
                <label>
                    <span>Атауы</span>
                    <input type="text" name="title" required minlength="3" maxlength="160">
                </label>
                <label>
                    <span>Сипаттама</span>
                    <textarea name="description" rows="4" required minlength="10" maxlength="1200"></textarea>
                </label>
                <div class="two-cols">
                    <label>
                        <span>URL</span>
                        <input type="url" name="url" maxlength="255" placeholder="https://">
                    </label>
                    <label>
                        <span>Skills</span>
                        <input type="text" name="skills" maxlength="255" placeholder="React, SMM, CRM">
                    </label>
                </div>
                <button class="btn btn-primary btn-full" type="submit">Portfolio қосу</button>
            </form>
            <div class="mini-list portfolio-list">
                <?php foreach ($portfolioItems as $item): ?>
                    <div>
                        <strong><?= e($item['title']) ?></strong>
                        <p><?= e($item['description']) ?></p>
                        <?php if (!empty($item['url'])): ?>
                            <a href="<?= e($item['url']) ?>" rel="noopener" target="_blank"><?= e($item['url']) ?></a>
                        <?php endif; ?>
                        <span><?= e($item['skills'] ?? '') ?></span>
                        <form class="js-ajax-form" action="ajax.php" method="post">
                            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                            <input type="hidden" name="action" value="portfolio_delete">
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                            <button class="btn btn-small btn-ghost" type="submit">Өшіру</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($portfolioItems)): ?>
                    <p class="muted">Portfolio әлі толтырылмаған.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <div class="section-heading">
                <span class="eyebrow">Reviews</span>
                <h2>Алынған пікірлер</h2>
            </div>
            <?php if (!empty($reviewsReceived)): ?>
                <div class="mini-list">
                    <?php foreach ($reviewsReceived as $review): ?>
                        <div>
                            <strong>★ <?= (int) $review['rating'] ?> · <?= e($review['project_title']) ?></strong>
                            <p><?= e($review['comment']) ?></p>
                            <span><?= e($review['reviewer_name']) ?> · <?= e($review['created_at']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">Әзірге review жоқ. Аяқталған жобалардан кейін пікірлер осында көрінеді.</p>
            <?php endif; ?>
        </section>
    </div>
</section>
