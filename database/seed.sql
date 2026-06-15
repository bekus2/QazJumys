/*
 * Project: QazJumys
 * File: seed.sql
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Seeds the first ten Kazakhstan-focused freelance categories.
 * RU: Заполняет первые десять категорий фриланса для Казахстана.
 */

USE qazjumys_portal;

INSERT INTO categories (slug, name, description, accent_color, sort_order, is_active, created_at, updated_at)
VALUES
    ('smm-content', 'SMM және контент', 'Әлеуметтік желі, мәтін, жоспар және бренд дауысы.', '#06b6d4', 10, 1, NOW(), NOW()),
    ('target-performance', 'Таргет / performance', 'Жарнама кабинеттері, аудитория және нәтиже аналитикасы.', '#f43f5e', 20, 1, NOW(), NOW()),
    ('website-development', 'Сайт әзірлеу', 'Landing page, корпоративтік сайт және интернет-дүкен.', '#2563eb', 30, 1, NOW(), NOW()),
    ('seo-google-ads', 'SEO / Google Ads', 'Іздеу жүйесі, жарнама науқаны және конверсия.', '#22c55e', 40, 1, NOW(), NOW()),
    ('mobile-reels', 'Мобилограф / Reels', 'Қысқа видео, түсірілім, сценарий және монтаж.', '#a855f7', 50, 1, NOW(), NOW()),
    ('frontend-web', 'Frontend / веб-әзірлеу', 'HTML, CSS, JavaScript және интерфейс логикасы.', '#0ea5e9', 60, 1, NOW(), NOW()),
    ('crm-automation', 'CRM / автоматтандыру', 'Процестер, интеграциялар және сату воронкасы.', '#14b8a6', 70, 1, NOW(), NOW()),
    ('one-c-development', '1C әзірлеу', 'Есеп, интеграция, бизнес-логика және қолдау.', '#f59e0b', 80, 1, NOW(), NOW()),
    ('ui-ux-graphic', 'UI/UX және графдизайн', 'Интерфейс, бренд визуалы және дизайн-жүйе.', '#ec4899', 90, 1, NOW(), NOW()),
    ('video-motion', 'Видеомонтаж / motion', 'Ролик, анимация, титр және motion-графика.', '#6366f1', 100, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    accent_color = VALUES(accent_color),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active),
    updated_at = NOW();
