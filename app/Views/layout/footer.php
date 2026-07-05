<?php
/**
 * Project: QazJumys
 * File: footer.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Closes the shared layout and loads frontend scripts.
 * RU: Закрывает общий шаблон и подключает фронтенд-скрипты.
 */
?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand footer-brand" href="<?= e(url_for('home')) ?>">
                <span class="brand-mark">Q</span>
                <span>QazJumys</span>
            </a>
            <p>Қазақстандағы digital жобаларға арналған фриланс маркетплейс.</p>
        </div>
        <div>
            <h2>Нарық</h2>
            <a href="<?= e(url_for('projects')) ?>">Ашық жобалар</a>
            <a href="<?= e(url_for('home')) ?>#categories">Категориялар</a>
            <a href="<?= e(url_for('home')) ?>#talent">Мамандар</a>
        </div>
        <div>
            <h2>Аккаунт</h2>
            <a href="<?= e(url_for('register')) ?>">Тіркелу</a>
            <a href="<?= e(url_for('login')) ?>">Кіру</a>
            <a href="<?= e(url_for('profile')) ?>">Профиль</a>
        </div>
        <div>
            <h2>Байланыс</h2>
            <?php if (!empty($config['app']['mail_to'])): ?>
                <p>Email: <?= e($config['app']['mail_to']) ?></p>
            <?php endif; ?>
            <?php if (!empty($config['app']['whatsapp_default'])): ?>
                <p>WhatsApp: <?= e($config['app']['whatsapp_default']) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© Beck Sarbassov. Барлық құқықтар қорғалған.</span>
        <span>2026-06-16</span>
    </div>
</footer>
<div class="toast" role="status" aria-live="polite"></div>
<script src="<?= e(asset('vendor/jquery-3.7.1.min.js')) ?>"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
