<?php
/**
 * Project: QazJumys
 * File: project-create.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Project publishing form for client accounts.
 * RU: Форма публикации проекта для аккаунтов заказчика.
 */
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Жаңа жоба</span>
        <h1>Тапсырманы нақты сипаттаңыз</h1>
        <p>Жақсы сипаттама орындаушыға бюджет, мерзім және нәтиже бойынша дұрыс ұсыныс беруге көмектеседі.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container narrow">
        <form class="panel form js-ajax-form" action="ajax.php" method="post" data-success-redirect="<?= e(url_for('dashboard')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
            <input type="hidden" name="action" value="project_create">
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
                <span>Жоба атауы</span>
                <input type="text" name="title" required minlength="6" maxlength="180" placeholder="Мысалы: Instagram контент жоспары және Reels түсірілімі">
            </label>
            <label>
                <span>Сипаттама</span>
                <textarea name="description" rows="7" required minlength="30" maxlength="3000" placeholder="Мақсат, күтілетін нәтиже, материалдар, мерзім және ерекше талаптарды жазыңыз"></textarea>
            </label>
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
            <label>
                <span>Мерзім, күн</span>
                <input type="number" name="deadline_days" min="1" max="180" value="14" required>
            </label>
            <button class="btn btn-primary btn-full" type="submit">Жобаны жариялау</button>
        </form>
    </div>
</section>
