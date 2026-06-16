# QazJumys

## English

QazJumys is a Kazakhstan-focused freelance marketplace for digital work. Version 1.2.0 includes unified member accounts, project publishing, proposal acceptance, work submission/completion workflow, protected messaging, protected file uploads, in-app/email notification logs, complaint handling, and a protected owner administration panel at `public/owner.php`.

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

4. Open the local site:

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

1. Upload project files to hosting.
2. Configure the web server document root to `public`. If hosting cannot point to `public`, keep `app`, `database`, `storage`, `.env`, and documentation protected from direct HTTP access.
3. Create a MySQL/MariaDB database with `utf8mb4_unicode_ci`.
4. Import:

```sql
database/schema.sql
database/seed.sql
```

Use `database/demo.sql` only for local/demo environments.

5. Create `.env` on the server and set production values:

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

6. Make `storage/uploads`, `storage/logs`, and `storage/cache` writable by PHP, but not publicly browsable.
7. Verify `owner.php` login, project publishing, proposal acceptance, messaging, upload/download, and completion workflow.
8. Change the default owner password.
9. Enable HTTPS and keep `.env`, backups, logs, and uploads protected.

### Upgrade from v1.1.0

Back up files and database first, then run:

```powershell
cmd /c 'type "database\upgrade_1_2_0.sql" | "C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot qazjumys_portal'
```

### Main Features

- Unified member account: one account can publish projects and submit proposals.
- Project workflow: `open -> in_progress -> submitted -> completed`.
- Proposal actions: send, accept, decline, complete.
- Messaging between project participants.
- Protected project file uploads and downloads.
- Complaint form and owner moderation queue.
- Owner panel: statistics, users, block/unblock, password reset, complaints, project status management, email logs, audit logs.
- Email notification logging, with optional PHP `mail()` sending when `MAIL_ENABLED=true`.
- Lightweight automated tests and GitHub Actions CI.

### Folder Structure

- `app/Config` - environment-driven configuration.
- `app/Core` - auth, CSRF, database, validation, upload, response helpers.
- `app/Repositories` - database access for users, projects, messages, files, complaints, notifications, owner tools.
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

Passwords are hashed with `password_hash()`. State-changing actions use CSRF tokens. SQL uses prepared statements. Uploads are MIME/size checked and stored outside `public`. Owner tools require an owner session. `.env`, logs, backups, and uploads must remain protected.

### Scaling Recommendations

Future improvements should add password self-change, SMTP provider integration, payment/escrow, richer dispute workflow, reviews, notifications over queues, pagination/search in owner tables, and a REST API only when needed.

## Русский

QazJumys — фриланс-маркетплейс для digital-задач в Казахстане. Версия 1.2.0 включает единый аккаунт участника, публикацию проектов, прием откликов, сдачу и завершение работы, защищенные сообщения, загрузку файлов, уведомления, жалобы и защищенную owner-панель `public/owner.php`.

### Требования

- PHP 8.1+ с `pdo_mysql`, `mbstring`, `fileinfo`, sessions.
- MySQL/MariaDB 10.4+.
- Веб-сервер, желательно с document root на папку `public`.
- Open Server Panel поддерживается для локального запуска.

### Локальный запуск в OpenServer

1. Скопируйте `.env.example` в `.env`.
2. Настройте базу и URL в `.env`.
3. Импортируйте `database/schema.sql`, затем `database/seed.sql`, затем при необходимости `database/demo.sql`.
4. Откройте `http://localhost:8080/qazjumys/` и `http://localhost:8080/qazjumys/owner.php`.

### Развертывание на хостинг

1. Загрузите файлы проекта.
2. Направьте домен на `public`.
3. Создайте базу MySQL/MariaDB в кодировке `utf8mb4_unicode_ci`.
4. Импортируйте `schema.sql` и `seed.sql`; `demo.sql` не используйте в production.
5. Создайте `.env` с production-настройками, отключите `APP_DEBUG`, настройте БД и почту.
6. Дайте PHP права записи на `storage/uploads`, `storage/logs`, `storage/cache`.
7. Проверьте вход owner, публикацию проекта, отклик, прием, сообщения, файлы и завершение.
8. Смените пароль owner сразу после первого входа.

### Автор

Автор: Beck Sarbassov  
Дата создания: 2026-06-16  
Последнее обновление: 2026-06-16  
Авторские права: © Beck Sarbassov. Все права защищены.
