# QazJumys

## English

QazJumys is a Kazakhstan-focused freelance marketplace for digital work. Version 1.3.0 provides a more complete operational flow: unified member accounts, project publishing, proposal shortlist/accept/withdraw/decline, project cancellation, work submission/completion, protected messaging, protected uploads, saved projects, saved searches, milestones, reviews, portfolio, verification requests, notification logs, complaints, owner statistics, and the protected owner panel at `public/owner.php`.

### Version 1.3.0 Added Functions

1. Saved projects/bookmarks for member accounts.
2. Saved project searches with reusable filters.
3. Project view and activity metrics.
4. Proposal shortlist workflow before acceptance.
5. Proposal withdrawal by the proposal author.
6. Project cancellation before completion.
7. Project milestones with due dates.
8. Milestone completion by project participants.
9. Reviews and ratings after completed projects.
10. Portfolio items on member profiles.
11. Profile verification requests.
12. Owner approval/rejection of verification requests.
13. Password change from the profile page.
14. Extended owner statistics for saved items, reviews, portfolio, and verification.
15. Improved responsive marketplace layout for desktop, tablet, and mobile screens.

### System Requirements

- PHP 8.1 or newer with `pdo_mysql`, `mbstring`, `fileinfo`, and sessions enabled.
- MySQL/MariaDB 10.4+.
- Web server pointing to the `public` directory where possible.
- Open Server Panel on Windows is supported for local work.

### Local Installation with OpenServer

1. Copy `.env.example` to `.env`.
2. Set database values in `.env`:

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qazjumys_portal
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://localhost:8080/qazjumys
MAIL_ENABLED=false
```

3. Import SQL in this order:

```powershell
cmd /c 'type "database\schema.sql" | "C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot'
cmd /c 'type "database\seed.sql" | "C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot qazjumys_portal'
cmd /c 'type "database\demo.sql" | "C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot qazjumys_portal'
```

4. Open:

```text
http://localhost:8080/qazjumys/
http://localhost:8080/qazjumys/owner.php
```

### Default Owner Account

For first local installation only:

- Login: `bek0435@gmail.com`
- Password: `0123456789+Aa`

Security note: these are initial development credentials. Change them immediately after the first login, especially before production deployment. Do not display these credentials on public pages.

### Production Hosting Deployment

1. Upload project files to the hosting account.
2. Set the web server document root to `public`. If the hosting control panel cannot point to `public`, protect `app`, `database`, `storage`, `.env`, logs, backups, and documentation from direct HTTP access with server rules.
3. Create a MySQL/MariaDB database with charset/collation `utf8mb4` / `utf8mb4_unicode_ci`.
4. Import `database/schema.sql`, then `database/seed.sql`.
5. Do not import `database/demo.sql` in production. Use it only for local testing.
6. Create `.env` on the server:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password
MAIL_ENABLED=true
MAIL_FROM=no-reply@your-domain.example
UPLOAD_MAX_BYTES=5242880
```

7. Make `storage/uploads`, `storage/logs`, and `storage/cache` writable by PHP.
8. Keep `storage/uploads` private. Downloads must go through `public/download.php`.
9. Enable HTTPS.
10. Log in to `owner.php`, change the default password, verify project publishing, proposal workflow, messaging, upload/download, reviews, verification, complaints, and owner moderation.
11. Set regular database and storage backups.

### Upgrade from v1.2.0

Back up files and database first, then run:

```powershell
cmd /c 'type "database\upgrade_1_3_0.sql" | "C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot qazjumys_portal'
```

If upgrading from v1.1.0, run `database/upgrade_1_2_0.sql` first, then `database/upgrade_1_3_0.sql`.

### Main Features

- Unified member account: one account can publish projects and submit proposals.
- Project workflow: `open -> in_progress -> submitted -> completed`, plus `cancelled`.
- Proposal actions: send, shortlist, withdraw, accept, decline, complete.
- Messaging between project participants.
- Protected project file uploads and downloads.
- Saved projects and saved searches.
- Project milestones.
- Reviews and profile rating recalculation.
- Portfolio items and verification requests.
- Complaint form and owner moderation queue.
- Owner panel: statistics, users, block/unblock, password reset, complaints, verification, project status management, email logs, audit logs.
- Email notification logging, with optional PHP `mail()` sending when `MAIL_ENABLED=true`.
- Lightweight automated tests and GitHub Actions CI.

### Folder Structure

- `app/Config` - environment-driven configuration.
- `app/Core` - auth, CSRF, database, validation, upload, response helpers.
- `app/Repositories` - database access for users, projects, engagement, messages, files, complaints, notifications, owner tools.
- `app/Views` - PHP templates.
- `public` - web entry points, assets, `owner.php`, `download.php`.
- `database` - schema, seed, demo, upgrade SQL.
- `storage` - private uploads, logs, cache.
- `tests` - lightweight CI checks.
- `.github/workflows` - GitHub Actions CI.

### Testing

```powershell
C:\OSPanel\modules\php\PHP_8.1\php.exe tests\run.php
```

GitHub Actions runs the same test suite on pushes and pull requests.

### Security Notes

Passwords are hashed with `password_hash()`. State-changing actions use CSRF tokens. SQL uses prepared statements. Uploads are MIME/size checked and stored outside `public`. Owner tools require an owner session. `.env`, logs, backups, and uploads must remain protected. Production deployments must use HTTPS and unique database credentials.

### Scaling Recommendations

Future improvements can add SMTP provider API integration, payment/escrow, richer dispute evidence workflow, pagination/search inside owner tables, notification queues, and a REST API only when needed.

## Русский

QazJumys - фриланс-маркетплейс для digital-задач в Казахстане. Версия 1.3.0 включает единый аккаунт участника, публикацию проектов, shortlist/прием/отзыв/отклонение откликов, отмену проекта, сдачу и завершение работы, защищенные сообщения, загрузку файлов, сохраненные проекты, сохраненные поиски, milestones, отзывы, портфолио, заявки на верификацию, уведомления, жалобы и защищенную owner-панель `public/owner.php`.

### Новые функции версии 1.3.0

1. Сохранение проектов в избранное.
2. Сохранение поисковых фильтров.
3. Метрики просмотров и активности проектов.
4. Shortlist откликов перед приемом.
5. Отзыв отклика исполнителем.
6. Отмена проекта до завершения.
7. Milestones по проектам.
8. Закрытие milestones участниками проекта.
9. Отзывы и пересчет рейтинга после завершения.
10. Портфолио в профиле.
11. Заявка на верификацию профиля.
12. Одобрение/отклонение верификации владельцем сайта.
13. Смена пароля в профиле.
14. Расширенная статистика owner-панели.
15. Улучшенная адаптивная верстка для компьютера, планшета и мобильного экрана.

### Требования

- PHP 8.1+ с `pdo_mysql`, `mbstring`, `fileinfo`, sessions.
- MySQL/MariaDB 10.4+.
- Веб-сервер, желательно с document root на папку `public`.
- Open Server Panel поддерживается для локального запуска на Windows.

### Локальный запуск в OpenServer

1. Скопируйте `.env.example` в `.env`.
2. Настройте `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL`.
3. Импортируйте `database/schema.sql`, затем `database/seed.sql`, затем при необходимости `database/demo.sql`.
4. Откройте `http://localhost:8080/qazjumys/` и `http://localhost:8080/qazjumys/owner.php`.

### Развертывание на хостинге

1. Загрузите файлы проекта на хостинг.
2. Направьте домен на папку `public`.
3. Если хостинг не позволяет выбрать `public`, закройте прямой доступ к `app`, `database`, `storage`, `.env`, логам и резервным копиям.
4. Создайте базу MySQL/MariaDB с `utf8mb4_unicode_ci`.
5. Импортируйте `database/schema.sql` и `database/seed.sql`.
6. Не импортируйте `database/demo.sql` на production.
7. Создайте `.env` с production-настройками, отключите `APP_DEBUG`, задайте подключение к БД и почту.
8. Дайте PHP права записи на `storage/uploads`, `storage/logs`, `storage/cache`.
9. Включите HTTPS.
10. Проверьте owner-вход, публикацию проекта, отклик, shortlist, прием, сообщения, файлы, milestones, отзывы, верификацию, жалобы и завершение работы.
11. Смените пароль owner сразу после первого входа.

### Обновление

Сначала сделайте резервную копию файлов и базы. Для перехода с v1.2.0 выполните `database/upgrade_1_3_0.sql`. Если база старее, сначала выполните `database/upgrade_1_2_0.sql`.

### Автор

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-21
Авторские права: © Beck Sarbassov. Все права защищены.
