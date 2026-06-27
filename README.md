# QazJumys

## English

QazJumys is a plain PHP freelance marketplace for project publishing, proposals, messaging, protected file exchange, reviews, portfolio, verification requests, complaints, and owner moderation.

Current version: `1.4.0`

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

4. Start the local PHP server with `public` as document root:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" -S 127.0.0.1:8014 -t public
```

5. Open:

- `http://127.0.0.1:8014/`
- `http://127.0.0.1:8014/owner.php`

### Owner Access Security

The seed file creates an initial owner account for first installation, but the public README does not publish the password. Set or reset the owner password through a private deployment procedure before production use, then change it after the first successful login. Do not place real passwords, SMTP secrets, API keys, or hosting credentials in public documentation or committed files.

### Hosting Deployment

1. Upload the project to hosting.
2. Point the domain document root to `public`.
3. If the host cannot point to `public`, deny direct HTTP access to `.env`, `app`, `database`, `storage`, logs, backups, and SQL dumps.
4. Create a MySQL/MariaDB database with `utf8mb4` and `utf8mb4_unicode_ci`.
5. Import `database/schema.sql`, then `database/seed.sql`.
6. Do not import `database/demo.sql` in production.
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
- `app/Repositories/ProjectRepository.php` - projects, proposals, workflow, permissions.
- `app/Repositories/FileRepository.php` - upload metadata and visibility rules.
- `app/Repositories/EngagementRepository.php` - saved projects, searches, milestones, reviews, portfolio, verification.
- `database/schema.sql` - full schema.
- `database/seed.sql` - production-safe initial data.
- `database/demo.sql` - local demo data only.
- `tests` - CI and local smoke tests.

### Security Notes

- Passwords use `password_hash()`.
- Sessions use HttpOnly cookies and SameSite=Lax.
- State-changing actions require CSRF.
- SQL access uses prepared statements.
- Blocked accounts cannot log in.
- Owner actions require owner session.
- Uploads are MIME/size checked.
- Direct upload browsing is denied by `storage/uploads/.htaccess`.
- Private URL prefixes are denied by `public/index.php` and `.htaccess` as defense in depth.
- Download access is checked by file type: `brief`, `proposal`, `delivery`.
- `.env`, logs, cache, backups, and uploaded files must stay out of public web access.

### Future Improvements

- Payment and escrow.
- External SMTP/provider API.
- Richer dispute evidence workflow.
- Owner table pagination and search.
- Queue-based notifications for high traffic.

## Русский

QazJumys - PHP-маркетплейс для фриланс-задач: публикация проектов, отклики, сообщения, защищённые файлы, отзывы, портфолио, верификация, жалобы и панель владельца.

Текущая версия: `1.4.0`

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
3. Импортируйте `schema.sql`, затем `seed.sql`, затем локально `demo.sql`.
4. Запустите:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" -S 127.0.0.1:8014 -t public
```

5. Откройте `http://127.0.0.1:8014/`.

### Развёртывание на хостинг

- Document root должен быть `public`.
- Если это невозможно, закройте прямой доступ к `.env`, `app`, `database`, `storage`, логам, backup и SQL-файлам.
- База данных должна быть `utf8mb4_unicode_ci`.
- На production импортируйте только `schema.sql` и `seed.sql`.
- `demo.sql` используйте только локально.
- Включите HTTPS.
- `APP_DEBUG=false`.
- `MAIL_ENABLED=false`, пока почта не проверена.
- После первого входа владелец должен сменить пароль через приватную процедуру.

### Автор

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-28
Авторские права: © Beck Sarbassov. Все права защищены.
