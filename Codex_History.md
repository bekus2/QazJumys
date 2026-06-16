# Codex_History.md

## 2026-06-16 — v1.1.0

### Краткое
Проект усилен до более готового freelance marketplace: обновлен визуальный стиль, добавлены расширенные поля проекта, фильтры, featured/urgent логика, карточки исполнителей, market stats, локальные демо-данные, OpenServer запуск и подробная документация по хостингу.

### Измененные файлы
- `.env.example`
- `.htaccess`
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`
- `app/bootstrap.php`
- `app/Config/config.php`
- `app/Core/Auth.php`
- `app/Core/Csrf.php`
- `app/Core/Database.php`
- `app/Core/Response.php`
- `app/Core/Validator.php`
- `app/Repositories/CategoryRepository.php`
- `app/Repositories/ProjectRepository.php`
- `app/Repositories/UserRepository.php`
- `app/Views/layout/header.php`
- `app/Views/layout/footer.php`
- `app/Views/home.php`
- `app/Views/auth-register.php`
- `app/Views/auth-login.php`
- `app/Views/projects.php`
- `app/Views/project-create.php`
- `app/Views/dashboard.php`
- `app/Views/profile.php`
- `database/schema.sql`
- `database/seed.sql`
- `database/demo.sql`
- `database/upgrade_1_1_0.sql`
- `public/.htaccess`
- `public/index.php`
- `public/ajax.php`
- `public/assets/css/style.css`
- `public/assets/js/app.js`

### Добавлено
- Расширенные поля проекта: тип оплаты, уровень опыта, навыки, локация, удаленный формат, featured, urgent.
- Расширенный поиск проектов по слову, категории, бюджету, типу, уровню и сортировке.
- Карточки исполнителей с headline, рейтингом, количеством работ, навыками и ставкой.
- Публичные marketplace counters.
- Отдельный `database/demo.sql` для локальных демо-пользователей, проектов и откликов.
- `database/upgrade_1_1_0.sql` для обновления базы v1.0.0.
- Локальный запуск через OpenServer URL `http://localhost:8080/qazjumys/`.
- Подробные инструкции импорта базы и деплоя на хостинг.

### Исправлено
- Снижен риск SQL ошибки `ONLY_FULL_GROUP_BY`: счетчики откликов теперь выбираются через подзапросы.
- Улучшена responsive верстка и защита от горизонтального overflow на mobile.
- Разделены production-safe seed и local demo data.
- Исправлен риск повреждения казахского/русского текста при SQL импорте через явное `utf8mb4`.

### Безопасность
- Сохранены `password_hash()`, session auth, HttpOnly cookies, SameSite=Lax, CSRF, prepared statements и output escaping.
- `database/demo.sql` документирован как локальный файл, не предназначенный для production.
- `.env` остается вне Git.
- Отклик теперь дополнительно проверяет, что проект существует и открыт.

### Примечания
- Admin panel, email notifications, private messaging, file uploads, payment/escrow, tests and CI remain future work.
- Перед production нужно импортировать только `schema.sql` и `seed.sql`, настроить `.env`, включить HTTPS и не загружать демо-данные.

## 2026-06-16 — v1.0.0

### Краткое
Создана первая рабочая версия казахстанского фриланс-портала QazJumys с публичным интерфейсом на казахском языке, регистрацией, входом, ролями заказчика и исполнителя, проектами, откликами и личным кабинетом.

### Измененные файлы
- Initial project files, documentation, PHP app, SQL schema, CSS, JavaScript and assets.

### Добавлено
- Адаптивная главная страница.
- 10 категорий digital-услуг.
- Регистрация и вход для двух ролей.
- Публикация проектов заказчиком.
- Поиск и просмотр открытых проектов.
- Отправка отклика исполнителем.
- Ролевой кабинет.
- Редактирование профиля.
- MySQL schema and seed.
- CSRF-защищенный logout через POST.

### Исправлено
- Не применимо, это первая версия.

### Безопасность
- Добавлены `password_hash()`, сессии, CSRF-токены, PDO prepared statements, серверная валидация и экранирование вывода.
- `.env` исключен из Git.

### Примечания
- Следующие версии должны добавить admin panel, notifications, messaging, file uploads, rate limiting and tests.
