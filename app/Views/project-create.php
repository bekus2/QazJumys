<?php
/**
 * Project: QazJumys
 * File: project-create.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Rich project publishing form for client accounts.
 * RU: Расширенная форма публикации проекта для аккаунтов заказчика.
 */
?>
<section class="page-hero">
    <div class="container page-hero-grid">
        <div>
            <span class="eyebrow">Жаңа жоба</span>
            <h1>Тапсырманы толық жариялау</h1>
            <p>Категория, бюджет, дағды, локация және мерзім дұрыс толса, орындаушы ұсынысы да нақты болады.</p>
        </div>
        <div class="page-stat">
            <strong>5</strong>
            <span>негізгі блок</span>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container form-layout">
        <aside class="side-note">
            <span class="eyebrow">Project brief</span>
            <h2>Жақсы brief уақытты үнемдейді</h2>
            <p>Бірінші нұсқада файл жүктеу мен төлем жүйесі жоқ, сондықтан міндетті мәтін, нәтиже, бюджет және мерзім анық жазылуы керек.</p>
        </aside>
        <form class="panel form js-ajax-form" action="ajax.php" method="post" data-success-redirect="<?= e(url_for('dashboard')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
            <input type="hidden" name="action" value="project_create">
            <div class="two-cols">
                <label>
                    <span>Категория</span>
                    <select name="category_id" required>
                        <option value="">Таңдаңыз</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Тип оплаты</span>
                    <select name="project_type" required>
                        <option value="fixed">Бір жолғы</option>
                        <option value="hourly">Сағаттық</option>
                    </select>
                </label>
            </div>
            <label>
                <span>Жоба атауы</span>
                <input type="text" name="title" required minlength="6" maxlength="180" placeholder="Мысалы: Instagram контент жоспары және Reels түсірілімі">
            </label>
            <label>
                <span>Сипаттама</span>
                <textarea name="description" rows="7" required minlength="30" maxlength="3000" placeholder="Мақсат, күтілетін нәтиже, материалдар, мерзім және ерекше талаптарды жазыңыз"></textarea>
            </label>
            <label>
                <span>Дағдылар</span>
                <input type="text" name="skills" required maxlength="500" placeholder="SMM, Reels, copywriting, Google Ads">
            </label>
            <div class="three-cols">
                <label>
                    <span>Тәжірибе деңгейі</span>
                    <select name="experience_level" required>
                        <option value="entry">Junior</option>
                        <option value="intermediate" selected>Middle</option>
                        <option value="expert">Expert</option>
                    </select>
                </label>
                <label>
                    <span>Локация</span>
                    <input type="text" name="location" maxlength="80" placeholder="Алматы немесе Қазақстан">
                </label>
                <label>
                    <span>Мерзім, күн</span>
                    <input type="number" name="deadline_days" min="1" max="180" value="14" required>
                </label>
            </div>
            <div class="two-cols">
                <label>
                    <span>Бюджеттен, ₸</span>
                    <input type="number" name="budget_min" min="0" step="1000" required>
                </label>
                <label>
                    <span>Бюджетке дейін, ₸</span>
                    <input type="number" name="budget_max" min="0" step="1000" required>
                </label>
            </div>
            <div class="check-row">
                <label>
                    <input type="checkbox" name="is_remote" value="1" checked>
                    <span>Қашықтан жұмыс болады</span>
                </label>
                <label>
                    <input type="checkbox" name="is_urgent" value="1">
                    <span>Жедел тапсырма</span>
                </label>
            </div>
            <button class="btn btn-primary btn-full" type="submit">Жобаны жариялау</button>
        </form>
    </div>
</section>
