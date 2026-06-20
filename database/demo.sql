/*
 * Project: QazJumys
 * File: demo.sql
 * Author: Beck Sarbassov
 * Version: 1.3.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-21
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Optional local demo users, projects, bids, messages, complaints, workflow states, saved items, milestones, reviews, portfolio, and verification queues.
 * RU: Дополнительные локальные демо-пользователи, проекты, отклики, сообщения, жалобы, workflow, сохранения, milestones, отзывы, портфолио и верификация.
 */

USE qazjumys_portal;

INSERT INTO users (
    role, status, name, email, password_hash, city, headline, bio, skills, hourly_rate,
    rating, reviews_count, completed_projects, response_time, is_verified, password_reset_required, created_at, updated_at
)
VALUES
    ('member', 'active', 'Alem Retail', 'client.demo@qazjumys.local', '$2y$10$x3COLIYU.wVA1737PO.2IOm.Z/z7PIlHZNa6W.hHB420/oMv0Ajvy', 'Алматы', 'Онлайн сатылым мен маркетингті дамытып жатқан жергілікті бизнес', 'Digital сатылым арнасын жүйелі түрде өсіруге арналған жобалар жариялайды.', 'e-commerce, retail, content, ads', NULL, 0.00, 0, 0, NULL, 1, 0, NOW(), NOW()),
    ('member', 'active', 'Nomad Clinic', 'clinic.demo@qazjumys.local', '$2y$10$x3COLIYU.wVA1737PO.2IOm.Z/z7PIlHZNa6W.hHB420/oMv0Ajvy', 'Астана', 'Клиникаға арналған CRM және жарнама процестерін дамытушы команда', 'Пациент жазылымын, жарнаманы және аналитиканы бір жүйеге жинауды жоспарлайды.', 'healthcare, crm, google ads, analytics', NULL, 0.00, 0, 0, NULL, 1, 0, NOW(), NOW()),
    ('member', 'active', 'Айдана SMM Pro', 'aidana.demo@qazjumys.local', '$2y$10$x3COLIYU.wVA1737PO.2IOm.Z/z7PIlHZNa6W.hHB420/oMv0Ajvy', 'Алматы', 'SMM стратегия, контент жоспар және reels сериялары', 'Шағын бизнеске контент жүйесін құрып, ай сайынғы жоспар мен жарнамаға дайын материал дайындайды.', 'SMM, copywriting, reels, Instagram, content plan', 9000.00, 4.90, 18, 31, '2 сағат ішінде', 1, 0, NOW(), NOW()),
    ('member', 'active', 'Dias Web Studio', 'dias.demo@qazjumys.local', '$2y$10$x3COLIYU.wVA1737PO.2IOm.Z/z7PIlHZNa6W.hHB420/oMv0Ajvy', 'Шымкент', 'Landing page, корпоративтік сайт және frontend интеграция', 'PHP, JavaScript және таза адаптивті интерфейстер арқылы бизнес сайттарын іске қосады.', 'PHP, JavaScript, HTML, CSS, landing, integrations', 12000.00, 4.80, 24, 44, '1 күн ішінде', 1, 0, NOW(), NOW()),
    ('member', 'active', 'Madina Ads', 'madina.demo@qazjumys.local', '$2y$10$x3COLIYU.wVA1737PO.2IOm.Z/z7PIlHZNa6W.hHB420/oMv0Ajvy', 'Қарағанды', 'Google Ads, performance және аналитика', 'Бюджетті бақылап, нақты лид пен өтінімге бағытталған жарнама жүйесін құрады.', 'Google Ads, Meta Ads, analytics, performance, SEO', 11000.00, 4.70, 15, 27, '3 сағат ішінде', 1, 0, NOW(), NOW()),
    ('member', 'active', 'Arman CRM Lab', 'arman.demo@qazjumys.local', '$2y$10$x3COLIYU.wVA1737PO.2IOm.Z/z7PIlHZNa6W.hHB420/oMv0Ajvy', 'Астана', 'CRM, автоматтандыру және 1C интеграция', 'Сату процесін картаға түсіріп, CRM өрістерін, воронканы және есептерді реттейді.', 'CRM, 1C, automation, API, business process', 15000.00, 4.95, 21, 36, '4 сағат ішінде', 1, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    role = IF(role = 'owner', role, 'member'),
    status = 'active',
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
       'Alem Retail жаңа маусымдық коллекцияны шығарады. 30 күндік контент жоспар, 12 reels сценарийі, визуал бағыты және жарнамаға дайын қысқа мәтіндер керек.',
       'fixed', 'intermediate', 'SMM, Reels, copywriting, content plan', 'Алматы',
       1, 1, 0, 180000.00, 320000.00, 21, 'open', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'smm-content'
WHERE client.email = 'client.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Интернет-дүкенге арналған Instagram контент және Reels пакеті');

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Медициналық клиникаға CRM воронка және өтінім есебі',
       'Клиникаға сайттан, Instagram-нан және телефоннан келетін өтінімдерді бір CRM процесіне жинау керек. Міндет: воронка кезеңдері, жауапты менеджерлер, еске салғыштар, UTM аналитика және апталық есеп форматы.',
       'fixed', 'expert', 'CRM, automation, analytics, API, sales funnel', 'Астана',
       1, 1, 1, 450000.00, 850000.00, 35, 'in_progress', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'crm-automation'
WHERE client.email = 'clinic.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Медициналық клиникаға CRM воронка және өтінім есебі');

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Қызметтер каталогы бар корпоративтік сайт',
       'B2B қызмет көрсететін компанияға жылдам, адаптивті және SEO-ға дайын сайт қажет. Беттер: басты бет, қызметтер, кейстер, байланыс формасы.',
       'fixed', 'intermediate', 'PHP, HTML, CSS, JavaScript, SEO, responsive', 'Шымкент',
       1, 0, 0, 380000.00, 650000.00, 28, 'submitted', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'website-development'
WHERE client.email = 'client.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Қызметтер каталогы бар корпоративтік сайт');

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, completed_at, created_at, updated_at
)
SELECT client.id, category.id,
       'Google Ads аудит және жаңа іздеу кампаниялары',
       'Қазіргі жарнама аккаунтында өтінім бағасы жоғары. Семантиканы тазалау, минус сөздер, конверсия трекингі және 3 жаңа іздеу кампаниясын іске қосу қажет.',
       'fixed', 'expert', 'Google Ads, SEO, analytics, conversion tracking', 'Қазақстан',
       1, 0, 1, 220000.00, 420000.00, 14, 'completed', NOW(), NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'seo-google-ads'
WHERE client.email = 'clinic.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Google Ads аудит және жаңа іздеу кампаниялары');

INSERT INTO projects (
    client_id, category_id, title, description, project_type, experience_level, skills, location,
    is_remote, is_featured, is_urgent, budget_min, budget_max, deadline_days, status, created_at, updated_at
)
SELECT client.id, category.id,
       'Мобильді қосымшаға UI kit және 8 негізгі экран',
       'Marketplace бағытындағы MVP үшін UI kit, түс жүйесі, компоненттер және 8 негізгі экран қажет. Нәтиже frontend әзірлеушіге түсінікті болуы керек.',
       'fixed', 'intermediate', 'UI/UX, mobile app, design system, prototype', 'Алматы',
       1, 1, 0, 300000.00, 550000.00, 24, 'open', NOW(), NOW()
FROM users client
INNER JOIN categories category ON category.slug = 'ui-ux-graphic'
WHERE client.email = 'client.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM projects p WHERE p.client_id = client.id AND p.title = 'Мобильді қосымшаға UI kit және 8 негізгі экран');

INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_days, status, created_at, updated_at)
SELECT p.id, f.id,
       'Контент жоспарын рубрика, сценарий және жарнамаға дайын hook бойынша құрып беремін. Алғашқы 3 күнде tone of voice пен визуал бағытты бекітіп аламыз.',
       260000.00, 18, 'shortlisted', NOW(), NOW()
FROM projects p
INNER JOIN users f ON f.email = 'aidana.demo@qazjumys.local'
WHERE p.title = 'Интернет-дүкенге арналған Instagram контент және Reels пакеті'
ON DUPLICATE KEY UPDATE cover_letter = VALUES(cover_letter), bid_amount = VALUES(bid_amount), delivery_days = VALUES(delivery_days), status = VALUES(status), updated_at = NOW();

INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_days, status, accepted_at, created_at, updated_at)
SELECT p.id, f.id,
       'CRM логикасын картадан бастап, воронка, өрістер, жауапты менеджерлер және апта сайынғы есептерге дейін реттеймін.',
       720000.00, 32, 'accepted', NOW(), NOW(), NOW()
FROM projects p
INNER JOIN users f ON f.email = 'arman.demo@qazjumys.local'
WHERE p.title = 'Медициналық клиникаға CRM воронка және өтінім есебі'
ON DUPLICATE KEY UPDATE cover_letter = VALUES(cover_letter), bid_amount = VALUES(bid_amount), delivery_days = VALUES(delivery_days), status = VALUES(status), accepted_at = VALUES(accepted_at), updated_at = NOW();

INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_days, status, accepted_at, created_at, updated_at)
SELECT p.id, f.id,
       'Сайтты таза PHP/HTML/CSS негізінде жасап, SEO мета, адаптив және байланыс формасын бірден дайындаймын.',
       520000.00, 24, 'accepted', NOW(), NOW(), NOW()
FROM projects p
INNER JOIN users f ON f.email = 'dias.demo@qazjumys.local'
WHERE p.title = 'Қызметтер каталогы бар корпоративтік сайт'
ON DUPLICATE KEY UPDATE cover_letter = VALUES(cover_letter), bid_amount = VALUES(bid_amount), delivery_days = VALUES(delivery_days), status = VALUES(status), accepted_at = VALUES(accepted_at), updated_at = NOW();

UPDATE projects p
INNER JOIN proposals pr ON pr.project_id = p.id AND pr.status = 'accepted'
SET p.accepted_proposal_id = pr.id,
    p.assigned_freelancer_id = pr.freelancer_id,
    p.started_at = COALESCE(p.started_at, NOW()),
    p.updated_at = NOW()
WHERE p.title IN ('Медициналық клиникаға CRM воронка және өтінім есебі', 'Қызметтер каталогы бар корпоративтік сайт');

INSERT INTO messages (project_id, proposal_id, sender_id, receiver_id, body, is_read, created_at)
SELECT p.id, pr.id, p.client_id, pr.freelancer_id,
       'Сәлем! Ұсынысыңыз ұнады, бірінші кезең бойынша нақты жоспар жібере аласыз ба?',
       0, NOW()
FROM projects p
INNER JOIN proposals pr ON pr.project_id = p.id AND pr.status = 'accepted'
WHERE p.title = 'Медициналық клиникаға CRM воронка және өтінім есебі'
  AND NOT EXISTS (SELECT 1 FROM messages m WHERE m.project_id = p.id);

INSERT INTO complaints (reporter_id, reported_user_id, project_id, subject, body, status, created_at, updated_at)
SELECT reporter.id, reported.id, p.id,
       'Жауап кешігіп жатыр',
       'Жоба бойынша жауап уақыты ұзақ болып кетті, owner тексеріп берсе.',
       'open', NOW(), NOW()
FROM users reporter
INNER JOIN users reported ON reported.email = 'arman.demo@qazjumys.local'
INNER JOIN projects p ON p.title = 'Медициналық клиникаға CRM воронка және өтінім есебі'
WHERE reporter.email = 'clinic.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM complaints c WHERE c.project_id = p.id AND c.subject = 'Жауап кешігіп жатыр');

INSERT INTO notifications (user_id, type, title, body, is_read, created_at)
SELECT u.id, 'demo', 'QazJumys demo дайын', 'Бұл локалды demo дерек: owner panel, хабарламалар және workflow тексеруге болады.', 0, NOW()
FROM users u
WHERE u.email IN ('client.demo@qazjumys.local', 'aidana.demo@qazjumys.local')
  AND NOT EXISTS (SELECT 1 FROM notifications n WHERE n.user_id = u.id AND n.type = 'demo');

UPDATE projects
SET views_count = CASE
        WHEN title LIKE '%CRM%' THEN 48
        WHEN title LIKE '%Instagram%' THEN 36
        WHEN title LIKE '%UI kit%' THEN 24
        ELSE 18
    END,
    last_activity_at = COALESCE(last_activity_at, updated_at, created_at, NOW())
WHERE title IN (
    'Интернет-дүкенге арналған Instagram контент және Reels пакеті',
    'Медициналық клиникаға CRM воронка және өтінім есебі',
    'Қызметтер каталогы бар корпоративтік сайт',
    'Google Ads аудит және жаңа іздеу кампаниялары',
    'Мобильді қосымшаға UI kit және 8 негізгі экран'
);

INSERT IGNORE INTO saved_projects (user_id, project_id, created_at)
SELECT u.id, p.id, NOW()
FROM users u
INNER JOIN projects p ON p.title IN (
    'Интернет-дүкенге арналған Instagram контент және Reels пакеті',
    'Мобильді қосымшаға UI kit және 8 негізгі экран'
)
WHERE u.email = 'aidana.demo@qazjumys.local';

INSERT INTO saved_searches (user_id, label, query_string, created_at)
SELECT u.id, 'Remote CRM және automation', 'page=projects&project_type=fixed&experience_level=expert&is_remote=1&sort=activity', NOW()
FROM users u
WHERE u.email = 'arman.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM saved_searches ss WHERE ss.user_id = u.id AND ss.label = 'Remote CRM және automation');

INSERT INTO project_milestones (project_id, owner_id, title, due_date, status, created_at, completed_at)
SELECT p.id, p.client_id, 'CRM funnel map бекіту', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'planned', NOW(), NULL
FROM projects p
WHERE p.title = 'Медициналық клиникаға CRM воронка және өтінім есебі'
  AND NOT EXISTS (SELECT 1 FROM project_milestones m WHERE m.project_id = p.id AND m.title = 'CRM funnel map бекіту');

INSERT INTO project_milestones (project_id, owner_id, title, due_date, status, created_at, completed_at)
SELECT p.id, p.client_id, 'Басты бет және қызметтер layout', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'done', NOW(), NOW()
FROM projects p
WHERE p.title = 'Қызметтер каталогы бар корпоративтік сайт'
  AND NOT EXISTS (SELECT 1 FROM project_milestones m WHERE m.project_id = p.id AND m.title = 'Басты бет және қызметтер layout');

INSERT IGNORE INTO reviews (project_id, reviewer_id, reviewee_id, rating, comment, created_at)
SELECT p.id, p.client_id, freelancer.id, 5,
       'Жарнама аудиті нақты болды, минус сөздер мен conversion tracking тез реттелді.',
       NOW()
FROM projects p
INNER JOIN users freelancer ON freelancer.email = 'madina.demo@qazjumys.local'
WHERE p.title = 'Google Ads аудит және жаңа іздеу кампаниялары';

INSERT IGNORE INTO reviews (project_id, reviewer_id, reviewee_id, rating, comment, created_at)
SELECT p.id, freelancer.id, p.client_id, 5,
       'Тапсырыс беруші brief пен access деректерін уақытында берді, жұмыс процесі анық болды.',
       NOW()
FROM projects p
INNER JOIN users freelancer ON freelancer.email = 'madina.demo@qazjumys.local'
WHERE p.title = 'Google Ads аудит және жаңа іздеу кампаниялары';

INSERT INTO portfolio_items (user_id, title, description, url, skills, created_at)
SELECT u.id, 'Retail content sprint', '30 күндік reels және Instagram content plan, жарнама hook және visual direction.', 'https://example.com/retail-content', 'SMM, Reels, copywriting', NOW()
FROM users u
WHERE u.email = 'aidana.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM portfolio_items pi WHERE pi.user_id = u.id AND pi.title = 'Retail content sprint');

INSERT INTO portfolio_items (user_id, title, description, url, skills, created_at)
SELECT u.id, 'Clinic CRM flow', 'Lead pipeline, manager reminders және weekly report workflow үшін CRM архитектурасы.', 'https://example.com/clinic-crm', 'CRM, API, automation', NOW()
FROM users u
WHERE u.email = 'arman.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM portfolio_items pi WHERE pi.user_id = u.id AND pi.title = 'Clinic CRM flow');

INSERT INTO verification_requests (user_id, status, note, owner_note, created_at, updated_at, reviewed_at)
SELECT u.id, 'pending', 'Портфолио, аяқталған жобалар және клиенттік ұсыныстар бойынша профильді тексеруге жіберемін.', NULL, NOW(), NOW(), NULL
FROM users u
WHERE u.email = 'dias.demo@qazjumys.local'
  AND NOT EXISTS (SELECT 1 FROM verification_requests vr WHERE vr.user_id = u.id AND vr.status = 'pending');
