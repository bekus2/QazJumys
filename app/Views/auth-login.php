<?php
/**
 * Project: QazJumys
 * File: auth-login.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Login page for existing portal users.
 * RU: Страница входа для существующих пользователей портала.
 */
?>
<section class="auth-section">
    <div class="container auth-grid compact">
        <div class="auth-copy">
            <span class="eyebrow">Кіру</span>
            <h1>Кабинетке кіру</h1>
            <p>Жобалар, ұсыныстар, профиль және нарықтағы жаңа тапсырмалар бір жерде.</p>
        </div>
        <form class="panel form js-ajax-form" action="ajax.php" method="post" data-success-redirect="<?= e(url_for('dashboard')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
            <input type="hidden" name="action" value="login">
            <label>
                <span>Email</span>
                <input type="email" name="email" required maxlength="180" autocomplete="email">
            </label>
            <label>
                <span>Құпиясөз</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn-primary btn-full" type="submit">Кіру</button>
            <p class="form-note">Аккаунт жоқ па? <a href="<?= e(url_for('register')) ?>">Тіркелу</a></p>
        </form>
    </div>
</section>
