<?php
/**
 * Project: QazJumys
 * File: auth-register.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Registration page for client and freelancer roles.
 * RU: Страница регистрации для ролей заказчика и исполнителя.
 */
?>
<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-copy">
            <span class="eyebrow">Аккаунт ашу</span>
            <h1>QazJumys аккаунтын ашу</h1>
            <p>Тапсырыс беруші жобаны жариялайды, орындаушы бюджет пен мерзімі бар ұсыныс жібереді.</p>
            <div class="auth-points">
                <span>CSRF қорғаныс</span>
                <span>Рөл бойынша кабинет</span>
                <span>Жобалар мен ұсыныстар</span>
            </div>
        </div>
        <form class="panel form js-ajax-form" action="ajax.php" method="post" data-success-redirect="<?= e(url_for('dashboard')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
            <input type="hidden" name="action" value="register">
            <div class="role-switch" role="radiogroup" aria-label="Рөл">
                <label>
                    <input type="radio" name="role" value="client" checked>
                    <span>Тапсырыс беруші</span>
                </label>
                <label>
                    <input type="radio" name="role" value="freelancer">
                    <span>Орындаушы</span>
                </label>
            </div>
            <label>
                <span>Аты-жөні</span>
                <input type="text" name="name" required minlength="2" maxlength="120" autocomplete="name">
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" required maxlength="180" autocomplete="email">
            </label>
            <label>
                <span>Қала</span>
                <input type="text" name="city" maxlength="80" placeholder="Алматы, Астана, Шымкент..." autocomplete="address-level2">
            </label>
            <label>
                <span>Құпиясөз</span>
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
            <button class="btn btn-primary btn-full" type="submit">Тіркелу</button>
            <p class="form-note">Аккаунтыңыз бар ма? <a href="<?= e(url_for('login')) ?>">Кіру</a></p>
        </form>
    </div>
</section>
