# Codex_History.md

## 2026-06-21 — v1.3.0

### Краткое
Проект усилен до более полноценного marketplace workflow: добавлены сохраненные проекты и поиски, shortlist/withdraw/cancel, milestones, reviews, portfolio, verification queue, смена пароля, расширенная owner-статистика, demo-data и улучшенная адаптивная верстка.

### Измененные файлы
- `app/Repositories/ProjectRepository.php`
- `app/Repositories/UserRepository.php`
- `app/Repositories/OwnerRepository.php`
- `app/Repositories/EngagementRepository.php`
- `app/Views/projects.php`
- `app/Views/dashboard.php`
- `app/Views/profile.php`
- `public/ajax.php`
- `public/index.php`
- `public/owner.php`
- `public/assets/css/style.css`
- `database/schema.sql`
- `database/seed.sql`
- `database/demo.sql`
- `database/upgrade_1_3_0.sql`
- `tests/run.php`
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `TASK.md`
- `AI_RULES.md`

### Добавлено
- Сохранение проектов.
- Сохранение поисковых фильтров.
- Метрики просмотров и активности проектов.
- Shortlist откликов.
- Отзыв отклика исполнителем.
- Отмена проекта до завершения.
- Milestones и закрытие milestones.
- Reviews после завершенных проектов.
- Portfolio items в профиле.
- Заявки на верификацию профиля.
- Owner approve/reject для верификации.
- Смена пароля в профиле.
- Расширенная owner-статистика по saved/reviews/portfolio/verification.
- Demo SQL для новых функций.

### Исправлено
- Расширен workflow откликов и проектов, чтобы процесс не заканчивался только приемом заявки.
- Dashboard и профиль теперь показывают больше практических действий без ручного перехода по скрытым состояниям.
- Улучшены мобильные сетки фильтров, карточек проектов, профильных панелей и owner-таблиц.

### Безопасность
- Новые действия идут через `ajax.php`, CSRF, session checks и repository permission checks.
- Смена пароля проверяет текущий пароль и сохраняет новый через `password_hash()`.
- Verification moderation доступна только owner.
- SQL-запросы новых функций используют prepared statements.

### Примечания
- Платежи/escrow, внешний SMTP API и расширенный dispute evidence workflow остаются следующими крупными этапами.

## 2026-06-16 — v1.2.0

### Краткое
Добавлен рабочий owner/admin контур, единая модель аккаунта, полный процесс отклика и выполнения работы, сообщения, защищенные файлы, уведомления, жалобы, automated tests и GitHub Actions CI.

### Измененные файлы
- `app/Core/Auth.php`
- `app/Core/Upload.php`
- `app/Repositories/ProjectRepository.php`
- `app/Repositories/UserRepository.php`
- `app/Repositories/NotificationRepository.php`
- `app/Repositories/MessageRepository.php`
- `app/Repositories/FileRepository.php`
- `app/Repositories/ComplaintRepository.php`
- `app/Repositories/OwnerRepository.php`
- `app/Views/dashboard.php`
- `app/Views/projects.php`
- `app/Views/profile.php`
- `public/ajax.php`
- `public/index.php`
- `public/owner.php`
- `public/download.php`
- `public/assets/css/style.css`
- `public/assets/js/app.js`
- `database/schema.sql`
- `database/seed.sql`
- `database/demo.sql`
- `database/upgrade_1_2_0.sql`
- `tests/run.php`
- `.github/workflows/ci.yml`
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `TASK.md`
- `AI_RULES.md`

### Добавлено
- `owner.php` с общей статистикой, пользователями, жалобами, проектами, email log и audit log.
- Блокировка/разблокировка пользователей и owner-сброс пароля.
- Единый аккаунт `member`: один пользователь может публиковать проекты и отправлять отклики.
- Workflow проекта: `open -> in_progress -> submitted -> completed`.
- Прием и отклонение откликов.
- Сообщения между участниками проекта.
- Защищенная загрузка и скачивание файлов через `storage/uploads` и `download.php`.
- Жалобы пользователей и owner-модерация.
- Уведомления в БД и журнал email-отправок.
- `tests/run.php` и GitHub Actions CI.

### Исправлено
- Убрана жесткая зависимость действий от ролей client/freelancer.
- Для native PDO prepares исправлены повторяющиеся named placeholders в новых запросах.
- Демо-данные обновлены под owner, unified accounts, workflow, сообщения и жалобы.

### Безопасность
- Owner-действия требуют owner-сессию.
- Заблокированные аккаунты не могут входить.
- Загрузки проверяются по MIME и размеру.
- Файлы не лежат в публичной директории и отдаются только после проверки доступа.
- Сохранены CSRF, prepared statements, `password_hash()` и output escaping.

### Примечания
- Самостоятельная смена пароля и полноценный SMTP-провайдер еще требуют отдельной доработки.
- Платежи/escrow и расширенный dispute workflow не входят в v1.2.0.

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

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-16
Авторские права: © Beck Sarbassov. Все права защищены.
