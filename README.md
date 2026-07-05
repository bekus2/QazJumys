# QazJumys

## English

QazJumys is a plain PHP freelance marketplace for project publishing, proposals, messaging, protected file exchange, reviews, portfolio, verification requests, complaints, and owner moderation.

Current version: `1.5.0`

### Version 1.5.0 Updates

1. Added login brute-force throttling: max 5 failures per email and 20 per IP within 15 minutes (`app/Core/RateLimiter.php`, new `login_attempts` table).
2. Blocked accounts now lose access immediately: every state-changing AJAX action re-validates account status against the database, not only the session snapshot.
3. Removed the committed owner password hash and personal contacts from the repository; the owner account is now created with `php bin/create_owner.php`.
4. Owner password reset no longer stores/emails the temporary password in the notification body; it is shown to the owner only once in the panel response.
5. Added baseline security headers on all pages: `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.
6. Secure session cookies are now enabled behind reverse proxies via `X-Forwarded-Proto`.
7. Removed artificial rating/review-count inflation on project completion; ratings now come only from real reviews.
8. Added error logging to `storage/logs/app-YYYY-MM-DD.log` instead of silently swallowing exceptions.
9. Added pagination to the public projects search (20 per page) with a total-results counter.
10. Escaped `%`/`_` wildcards in project search input.
11. CI now runs real database and workflow smoke tests against MariaDB in GitHub Actions, in addition to syntax/integrity checks.
12. Added `database/upgrade_1_5_0.sql`, `LICENSE`, and updated all project documentation.

### Version 1.4.0 Updates

1. Hardened `acceptProposal()` so only `sent` or `shortlisted` proposals on `open` projects can be accepted.
2. Blocked repeat acceptance when a project is already `in_progress`, `submitted`, `completed`, or `cancelled`.
3. Fixed proposal ID return after proposal creation by storing `lastInsertId()` before activity updates.
4. Fixed milestone ID return after milestone creation.
5. Updated participant checks so `declined` and `withdrawn` proposals no longer grant messaging/file access.
6. Added pair-based message permission checks with `canMessage()`.
7. Split protected file rules for `brief`, `proposal`, and `delivery` files.
8. Added upload authorization checks before files are stored.
9. Updated `download.php` to use file-level visibility rules.
10. Added proposal-file upload UI for performers.
11. Added local DB/install smoke test: `tests/db_smoke.php`.
12. Added local HTTP/security smoke test: `tests/http_smoke.php`.
13. Added local workflow smoke test: `tests/workflow_smoke.php`.
14. Updated `.env.example` for the current OpenServer PHP/MariaDB setup.
15. Removed public README exposure of initial owner credentials.

### System Requirements

- PHP 8.1 or newer. This computer currently has OpenServer PHP at `C:\OSPanel\modules\PHP-8.4\php.exe`.
- PHP extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `session`.
- MariaDB/MySQL. This computer currently uses `C:\OSPanel\modules\MariaDB-10.4\bin\mysql.exe`.
- MySQL/MariaDB database with `utf8mb4` charset and `utf8mb4_unicode_ci` collation.
- Web document root should point to `public` whenever possible.

### Local OpenServer Setup

1. Create `.env` from `.env.example`.
2. For this computer, the local values are:

```ini
APP_ENV=local
APP_DEBUG=false
APP_URL=http://127.0.0.1:8014
DB_HOST=127.0.1.14
DB_PORT=3306
DB_DATABASE=qazjumys_portal
DB_USERNAME=root
DB_PASSWORD=
MAIL_ENABLED=false
```

3. Create/import the database:

```powershell
cmd /c "type database\schema.sql | C:\OSPanel\modules\MariaDB-10.4\bin\mysql.exe --host=127.0.1.14 --port=3306 --default-character-set=utf8mb4 -uroot"
cmd /c "type database\seed.sql | C:\OSPanel\modules\MariaDB-10.4\bin\mysql.exe --host=127.0.1.14 --port=3306 --default-character-set=utf8mb4 -uroot qazjumys_portal"
cmd /c "type database\demo.sql | C:\OSPanel\modules\MariaDB-10.4\bin\mysql.exe --host=127.0.1.14 --port=3306 --default-character-set=utf8mb4 -uroot qazjumys_portal"
```

If you upgrade an existing v1.4.0 database, also run `database/upgrade_1_5_0.sql`.

3a. Create the owner account (interactive, credentials are never stored in the repository):

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" bin\create_owner.php
```

4. Start the local PHP server with `public` as document root:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" -S 127.0.0.1:8014 -t public
```

5. Open:

- `http://127.0.0.1:8014/`
- `http://127.0.0.1:8014/owner.php`

### Owner Access Security

Since v1.5.0 the seed file no longer creates an owner account and no password hash is committed to the repository. Create or reset the owner with the interactive CLI installer:

```powershell
php bin/create_owner.php
```

Do not place real passwords, password hashes, SMTP secrets, API keys, personal contacts, or hosting credentials in public documentation or committed files. Login attempts are throttled automatically (5 failures per email / 20 per IP within 15 minutes).

### Hosting Deployment

1. Upload the project to hosting.
2. Point the domain document root to `public`.
3. If the host cannot point to `public`, deny direct HTTP access to `.env`, `app`, `database`, `storage`, logs, backups, and SQL dumps.
4. Create a MySQL/MariaDB database with `utf8mb4` and `utf8mb4_unicode_ci`.
5. Import `database/schema.sql`, then `database/seed.sql` (for upgrades from v1.4.0 run `database/upgrade_1_5_0.sql`).
6. Do not import `database/demo.sql` in production.
6a. Create the owner account with `php bin/create_owner.php`.
7. Create a protected server-side `.env` with production DB credentials.
8. Set `APP_DEBUG=false`.
9. Keep `MAIL_ENABLED=false` until email delivery is verified.
10. Enable HTTPS.
11. Make `storage/uploads`, `storage/logs`, and `storage/cache` writable by PHP.
12. Keep uploads private. Files must be downloaded only through `public/download.php`.
13. Configure backups for database and storage.

### Testing

Run the lightweight CI test:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\run.php
```

Run local installation/database checks:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\db_smoke.php
```

Run local workflow checks:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\workflow_smoke.php
```

Run HTTP and direct-access checks after the local server is running:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\http_smoke.php http://127.0.0.1:8014
```

### Main Modules

- `public/index.php` - public front controller.
- `public/ajax.php` - CSRF-protected state-changing endpoint.
- `public/owner.php` - protected owner panel.
- `public/download.php` - protected file downloads.
- `bin/create_owner.php` - CLI installer for the owner account.
- `app/Core/RateLimiter.php` - login brute-force throttling.
- `app/Repositories/ProjectRepository.php` - projects, proposals, workflow, permissions.
- `app/Repositories/FileRepository.php` - upload metadata and visibility rules.
- `app/Repositories/EngagementRepository.php` - saved projects, searches, milestones, reviews, portfolio, verification.
- `database/schema.sql` - full schema.
- `database/seed.sql` - production-safe initial data (categories only, no accounts).
- `database/demo.sql` - local demo data only.
- `tests` - CI and local smoke tests.

### Security Notes

- Passwords use `password_hash()`.
- Login attempts are rate limited per email and per IP.
- Sessions use HttpOnly cookies and SameSite=Lax; Secure is applied on HTTPS, including behind proxies (`X-Forwarded-Proto`).
- State-changing actions require CSRF.
- Account status is re-validated against the database on every state-changing action, so blocking applies to open sessions immediately.
- SQL access uses prepared statements; LIKE wildcards in search input are escaped.
- Blocked accounts cannot log in.
- Owner actions require owner session.
- No credentials or password hashes are committed to the repository; the owner is created via `bin/create_owner.php`.
- Uploads are MIME/size checked.
- Direct upload browsing is denied by `storage/uploads/.htaccess`.
- Private URL prefixes are denied by `public/index.php` and `.htaccess` as defense in depth.
- Download access is checked by file type: `brief`, `proposal`, `delivery`.
- Baseline security headers (CSP, X-Frame-Options, nosniff, Referrer-Policy) are sent on every page.
- Errors are logged to `storage/logs/` and never shown to visitors when `APP_DEBUG=false`.
- `.env`, logs, cache, backups, and uploaded files must stay out of public web access.

### Continuous Integration

GitHub Actions runs two jobs on every push/PR:

1. `php-checks` - PHP lint plus project integrity assertions (`tests/run.php`).
2. `db-workflow-checks` - imports the schema into a MariaDB service container and runs `tests/db_smoke.php` and `tests/workflow_smoke.php` against a real database.

### Future Improvements

- Payment and escrow.
- External SMTP/provider API and email verification at registration.
- Self-service password reset by email.
- Richer dispute evidence workflow.
- Owner table pagination and search.
- Full-text search via `MATCH ... AGAINST` on the existing FULLTEXT index.
- Queue-based notifications for high traffic.

## Русский

QazJumys - PHP-маркетплейс для фриланс-задач: публикация проектов, отклики, сообщения, защищённые файлы, отзывы, портфолио, верификация, жалобы и панель владельца.

Текущая версия: `1.5.0`

### Что добавлено и исправлено в 1.5.0

1. Защита входа от перебора паролей: максимум 5 неудач по email и 20 по IP за 15 минут (`app/Core/RateLimiter.php`, новая таблица `login_attempts`).
2. Блокировка аккаунта действует мгновенно: каждое изменяющее AJAX-действие перепроверяет статус аккаунта в базе, а не только в сессии.
3. Из репозитория убраны хеш пароля владельца и личные контакты; owner-аккаунт создается командой `php bin/create_owner.php`.
4. Временный пароль при сбросе больше не сохраняется и не отправляется в теле уведомления — он показывается владельцу один раз в панели.
5. Добавлены базовые security-заголовки на всех страницах: `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.
6. Secure-куки сессии работают за reverse-proxy через `X-Forwarded-Proto`.
7. Убрана искусственная накрутка рейтинга и счетчика отзывов при завершении проекта — рейтинг считается только по реальным отзывам.
8. Добавлено логирование ошибок в `storage/logs/app-YYYY-MM-DD.log` вместо «тихого» проглатывания исключений.
9. Добавлена пагинация поиска проектов (20 на страницу) и честный счетчик результатов.
10. Экранируются `%`/`_` в поисковом вводе (LIKE wildcard).
11. CI теперь запускает реальные DB/workflow smoke-тесты на MariaDB в GitHub Actions.
12. Добавлены `database/upgrade_1_5_0.sql`, файл `LICENSE`, обновлена вся документация.

### Что добавлено и исправлено в 1.4.0

1. Усилен приём отклика: принять можно только `sent` или `shortlisted` отклик по открытому проекту.
2. Запрещён повторный приём, если проект уже в работе, на проверке, завершён или отменён.
3. Исправлен возврат ID нового отклика после `createProposal()`.
4. Исправлен возврат ID нового milestone.
5. `declined` и `withdrawn` отклики больше не дают доступ к сообщениям и файлам.
6. Добавлена проверка пары участников для сообщений.
7. Разделены правила доступа для `brief`, `proposal`, `delivery` файлов.
8. Загрузка файла проверяет права до сохранения файла.
9. `download.php` использует отдельные правила видимости файлов.
10. В кабинете добавлена загрузка proposal-файла исполнителем.
11. Добавлен DB smoke-test.
12. Добавлен HTTP smoke-test.
13. Добавлен workflow smoke-test полного процесса работы.
14. Обновлён `.env.example` под текущий OpenServer.
15. Публичный README больше не содержит пароль владельца.

### Локальный запуск

1. Скопируйте `.env.example` в `.env`.
2. Проверьте `APP_URL`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
3. Импортируйте `schema.sql`, затем `seed.sql`, затем локально `demo.sql` (при обновлении с v1.4.0 — ещё `upgrade_1_5_0.sql`).
4. Создайте owner-аккаунт: `php bin/create_owner.php`.
5. Запустите:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" -S 127.0.0.1:8014 -t public
```

6. Откройте `http://127.0.0.1:8014/`.

### Развёртывание на хостинг

- Document root должен быть `public`.
- Если это невозможно, закройте прямой доступ к `.env`, `app`, `database`, `storage`, логам, backup и SQL-файлам.
- База данных должна быть `utf8mb4_unicode_ci`.
- На production импортируйте только `schema.sql` и `seed.sql`.
- `demo.sql` используйте только локально.
- Owner-аккаунт создавайте через `php bin/create_owner.php` — учетные данные не хранятся в репозитории.
- Включите HTTPS.
- `APP_DEBUG=false`.
- `MAIL_ENABLED=false`, пока почта не проверена.
- Реальные контакты (email, WhatsApp) задавайте только в приватном `.env`.

### Автор

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-07-05
Авторские права: © Beck Sarbassov. Все права защищены.
