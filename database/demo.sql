/*
 * Project: QazJumys
 * File: demo.sql
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Optional local demo users, projects, and bids for development previews.
 * RU: Дополнительные локальные демо-пользователи, проекты и отклики для проверки.
 */

USE qazjumys_portal;

INSERT INTO users (
    role, name, email, password_hash, city, headline, bio, skills, hourly_rate,
    rating, reviews_count, completed_projects, response_time, is_verified, created_at, updated_at
)
VALUES
    ('client', 'Alem Retail', 'client.demo@qazjumys.local', '$2y$10$1crJeykuLygYb8UoxnLgIezGLat19CaER4FpHp4ymQklQGcUJVVFu', 'Алматы', 'Онлайн сатылым мен маркетингті дамытып жатқан жергілікті бизнес', 'Digital сатылым арнасын жүйелі түрде өсіруге арналған жобалар жариялайды.', 'e-commerce, retail, content, ads', NULL, 0.00, 0, 0, NULL, 1, NOW(), NOW()),
    ('client', 'Nomad Clinic', 'clinic.demo@qazjumys.local', '$2y$10$1crJeykuLygYb8UoxnLgIezGLat19CaER4FpHp4ymQklQGcUJVVFu', 'Астана', 'Клиникаға арналған CRM және жарнама процестерін дамытушы команда', 'Пациент жазылымын, жарнаманы және аналитиканы бір жүйеге жинауды жоспарлайды.', 'healthcare, crm, google ads, analytics', NULL, 0.00, 0, 0, NULL, 1, NOW(), NOW()),
    ('freelancer', 'Айдана SMM Pro', 'aidana.demo@qazjumys.local', '$2y$10$1crJeykuLygYb8UoxnLgIezGLat19CaER4FpHp4ymQklQGcUJVVFu', 'Алматы', 'SMM стратегия, контент жоспар және reels сериялары', 'Шағын бизнеске контент жүйесін құрып, ай сайынғы жоспар мен жарнамаға дайын материал дайындайды.', 'SMM, copywriting, reels, Instagram, content plan', 9000.00, 4.90, 18, 31, '2 сағат ішінде', 1, NOW(), NOW()),
    ('freelancer', 'Dias Web Studio', 'dias.demo@qazjumys.local', '$2y$10$1crJeykuLygYb8UoxnLgIezGLat19CaER4FpHp4ymQklQGcUJVVFu', 'Шымкент', 'Landing page, корпоративтік сайт және frontend интеграция', 'PHP, JavaScript және таза адаптивті интерфейстер арқылы бизнес сайттарын іске қосады.', 'PHP, JavaScript, HTML, CSS, landing, integrations', 12000.00, 4.80, 24, 44, '1 күн ішінде', 1, NOW(), NOW()),
    ('freelancer', 'Madina Ads', 'madina.demo@qazjumys.local', '$2y$10$1crJeykuLygYb8UoxnLgIezGLat19CaER4FpHp4ymQklQGcUJVVFu', 'Қарағанды', 'Google Ads, performance және аналитика', 'Бюджетті бақылап, нақты лид пен өтінімге бағытталған жарнама жүйесін құрады.', 'Google Ads, Meta Ads, analytics, performance, SEO', 11000.00, 4.70, 15, 27, '3 сағат ішінде', 1, NOW(), NOW()),
    ('freelancer', 'Arman CRM Lab', 'arman.demo@qazjumys.local', '$2y$10$1crJeykuLygYb8UoxnLgIezGLat19CaER4FpHp4ymQklQGcUJVVFu', 'Астана', 'CRM, автоматтандыру және 1C интеграция', 'Сату процесін картаға түсіріп, CRM өрістерін, воронканы және есептерді реттейді.', 'CRM, 1C, automation, API, business process', 15000.00, 4.95, 21, 36, '4 сағат ішінде', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    city = VALUES(city),
    headline = VALUES(headline),
    bio = VALUES(bio),
    skills = VALUES(skills),
    hourly_rate = VALUES(hourly_rate),
    rating = VALUES(rating),
    reviews_count = VALUES(reviews_count),
    completed_projects = VALUES(completed_projects),
    response_time = VALUES(response_time),
    is_verified = VALUES(is_verified),
    updated_at = NOW();

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Интернет-дүкенге арналған Instagram контент және Reels пакеті',
       'Alem Retail жаңа маусымдық коллекцияны шығарады. 30 күндік контент жоспар, 12 reels сценарийі, визуал бағыты және жарнамаға дайын қысқа мәтіндер керек. Материалдар бар, бірақ tone of voice пен рубриканы жүйелеу қажет.',
       'fixed', 'intermediate', 'SMM, Reels, copywriting, content plan', 'Алматы',
       1, 1, 0, 180000.00, 320000.00, 21, 'open', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'smm-content'
WHERE client.email = 'client.demo@qazjumys.local'
  AND NOT EXISTS (
      SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Интернет-дүкенге арналған Instagram контент және Reels пакеті'
  );

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Медициналық клиникаға CRM воронка және өтінім есебі',
       'Клиникаға сайттан, Instagram-нан және телефоннан келетін өтінімдерді бір CRM процесіне жинау керек. Міндет: воронка кезеңдері, жауапты менеджерлер, еске салғыштар, UTM аналитика және апталық есеп форматы.',
       'fixed', 'expert', 'CRM, automation, analytics, API, sales funnel', 'Астана',
       1, 1, 1, 450000.00, 850000.00, 35, 'open', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'crm-automation'
WHERE client.email = 'clinic.demo@qazjumys.local'
  AND NOT EXISTS (
      SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Медициналық клиникаға CRM воронка және өтінім есебі'
  );

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Қызметтер каталогы бар корпоративтік сайт',
       'B2B қызмет көрсететін компанияға жылдам, адаптивті және SEO-ға дайын сайт қажет. Беттер: басты бет, қызметтер, кейстер, байланыс формасы. Дизайн таза, сенімді және мобильде ыңғайлы болуы керек.',
       'fixed', 'intermediate', 'PHP, HTML, CSS, JavaScript, SEO, responsive', 'Шымкент',
       1, 0, 0, 380000.00, 650000.00, 28, 'open', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'website-development'
WHERE client.email = 'client.demo@qazjumys.local'
  AND NOT EXISTS (
      SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Қызметтер каталогы бар корпоративтік сайт'
  );

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Google Ads аудит және жаңа іздеу кампаниялары',
       'Қазіргі жарнама аккаунтында өтінім бағасы жоғары. Семантиканы тазалау, минус сөздер, конверсия трекингі және 3 жаңа іздеу кампаниясын іске қосу қажет. Есепті түсінікті форматта беру маңызды.',
       'fixed', 'expert', 'Google Ads, SEO, analytics, conversion tracking', 'Қазақстан',
       1, 0, 1, 220000.00, 420000.00, 14, 'open', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'seo-google-ads'
WHERE client.email = 'clinic.demo@qazjumys.local'
  AND NOT EXISTS (
      SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Google Ads аудит және жаңа іздеу кампаниялары'
  );

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Мобильді қосымшаға UI kit және 8 негізгі экран',
       'Marketplace бағытындағы MVP үшін UI kit, түс жүйесі, компоненттер және 8 негізгі экран қажет. Нәтиже frontend әзірлеушіге түсінікті болуы керек: states, empty screens, mobile-first layout.',
       'fixed', 'intermediate', 'UI/UX, mobile app, design system, prototype', 'Алматы',
       1, 1, 0, 300000.00, 550000.00, 24, 'open', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'ui-ux-graphic'
WHERE client.email = 'client.demo@qazjumys.local'
  AND NOT EXISTS (
      SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Мобильді қосымшаға UI kit және 8 негізгі экран'
  );

INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_days, status, created_at, updated_at)
SELECT p.id, f.id,
       'Контент жоспарын рубрика, сценарий және жарнамаға дайын hook бойынша құрып беремін. Алғашқы 3 күнде tone of voice пен визуал бағытты бекітіп аламыз.',
       260000.00, 18, 'shortlisted', NOW(), NOW()
FROM projects p
INNER JOIN users f ON f.email = 'aidana.demo@qazjumys.local'
WHERE p.title = 'Интернет-дүкенге арналған Instagram контент және Reels пакеті'
ON DUPLICATE KEY UPDATE
    cover_letter = VALUES(cover_letter),
    bid_amount = VALUES(bid_amount),
    delivery_days = VALUES(delivery_days),
    status = VALUES(status),
    updated_at = NOW();

INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_days, status, created_at, updated_at)
SELECT p.id, f.id,
       'CRM логикасын картадан бастап, воронка, өрістер, жауапты менеджерлер және апта сайынғы есептерге дейін реттеймін. Қажет болса API интеграциясын бөлек кезеңге бөлемін.',
       720000.00, 32, 'sent', NOW(), NOW()
FROM projects p
INNER JOIN users f ON f.email = 'arman.demo@qazjumys.local'
WHERE p.title = 'Медициналық клиникаға CRM воронка және өтінім есебі'
ON DUPLICATE KEY UPDATE
    cover_letter = VALUES(cover_letter),
    bid_amount = VALUES(bid_amount),
    delivery_days = VALUES(delivery_days),
    status = VALUES(status),
    updated_at = NOW();

INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_days, status, created_at, updated_at)
SELECT p.id, f.id,
       'Сайтты таза PHP/HTML/CSS негізінде жасап, SEO мета, адаптив және байланыс формасын бірден дайындаймын. Дизайнды бизнес стиліне сай ұстаймын.',
       520000.00, 24, 'sent', NOW(), NOW()
FROM projects p
INNER JOIN users f ON f.email = 'dias.demo@qazjumys.local'
WHERE p.title = 'Қызметтер каталогы бар корпоративтік сайт'
ON DUPLICATE KEY UPDATE
    cover_letter = VALUES(cover_letter),
    bid_amount = VALUES(bid_amount),
    delivery_days = VALUES(delivery_days),
    status = VALUES(status),
    updated_at = NOW();
