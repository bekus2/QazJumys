<?php
/**
 * Project: QazJumys
 * File: footer.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
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
            <p>Қазақстандағы digital жобаларға арналған тәуелсіз еңбек порталы.</p>
        </div>
        <div>
            <h2>Бағыттар</h2>
            <p>SMM, жарнама, сайт, SEO, CRM, 1C, дизайн және видео.</p>
        </div>
        <div>
            <h2>Байланыс</h2>
            <p>Email: <?= e($config['app']['mail_to'] ?? 'bek0435@gmail.com') ?></p>
            <p>WhatsApp: <?= e($config['app']['whatsapp_default'] ?? '+77075080762') ?></p>
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
