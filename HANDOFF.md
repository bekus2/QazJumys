# HANDOFF.md

## Project
QazJumys is a Kazakhstan-focused freelance marketplace for digital services. Version 1.1.0 includes public browsing, role-based registration/login, project publishing, advanced project filtering, proposal sending, profile editing, featured/urgent project flags, freelancer preview cards, and local demo marketplace data.

## Technologies
- PHP 8.1+ plain PHP application
- MySQL/MariaDB with PDO
- HTML5, CSS3, JavaScript
- jQuery and AJAX
- Apache/Nginx with `public/` as the preferred document root

## Structure
- `public/index.php` is the front controller.
- `public/ajax.php` handles POST AJAX actions.
- `app/bootstrap.php` loads env, sessions, helpers, and class loading.
- `app/Core/` contains DB, auth, CSRF, validation, and response utilities.
- `app/Repositories/` contains SQL access for users, categories, projects, proposals, marketplace stats.
- `app/Views/` contains PHP templates.
- `database/schema.sql` creates the full v1.1.0 schema.
- `database/seed.sql` seeds production-safe categories.
- `database/demo.sql` seeds local preview users/projects/proposals.
- `database/upgrade_1_1_0.sql` upgrades an existing v1.0.0 database.

## Important Files
- `.env.example`
- `.htaccess`
- `public/.htaccess`
- `app/bootstrap.php`
- `app/Core/Auth.php`
- `app/Core/Csrf.php`
- `app/Repositories/ProjectRepository.php`
- `app/Repositories/UserRepository.php`
- `public/assets/css/style.css`
- `public/assets/js/app.js`
- `database/schema.sql`
- `database/seed.sql`
- `database/demo.sql`
- `database/upgrade_1_1_0.sql`

## Install and Run
1. Copy `.env.example` to `.env`.
2. Configure MySQL/MariaDB credentials.
3. Import `database/schema.sql`.
4. Import `database/seed.sql`.
5. Import `database/demo.sql` only for local preview.
6. Open the project through a server pointing to `public/`.

## OpenServer on This Computer
Current working OpenServer URL:

```text
http://localhost:8080/qazjumys/
```

Current junction:

```text
C:\OSPanel\domains\localhost\qazjumys -> C:\Users\Beck-S\Documents\GitHub\Experimental-Project-002
```

Local database:

```text
DB_DATABASE=qazjumys_portal
DB_USERNAME=root
DB_PASSWORD=
DB_HOST=127.0.0.1
DB_PORT=3306
```

Use `--default-character-set=utf8mb4` when importing SQL to protect Kazakh/Russian text.

## Deployment
Set the domain document root to `public/` when possible. If shared hosting cannot change document root, keep the root `.htaccess`; it routes requests to `public/`. Create the database, import `schema.sql`, import `seed.sql`, configure `.env`, set `APP_ENV=production`, set `APP_DEBUG=false`, enable HTTPS, and do not import `demo.sql` on production.

## Configuration
Main configuration is loaded from `.env` by `app/Config/config.php`. Do not commit `.env`.

## Do Not Change Without Understanding
- Session and CSRF logic in `app/bootstrap.php` and `app/Core/Csrf.php`.
- Role checks and POST-only actions in `public/ajax.php`.
- SQL relationships in `database/schema.sql`.
- Project search and counter queries in `app/Repositories/ProjectRepository.php`.
- Public/private folder separation between `public/` and `app/`, `database/`, `storage/`.

## Unfinished Tasks
- Admin moderation panel is not included.
- Email notifications are not implemented.
- Direct client-freelancer messaging is not implemented.
- File uploads are not implemented.
- Payment, escrow, and dispute logic are not implemented.
- Automated test suite is not included yet.

## Future Improvements
- Add admin dashboard, audit logs, and moderation workflows.
- Add messaging, notifications, project milestones, and accepted-proposal workflow.
- Add rate limiting for auth/proposal forms.
- Add private file uploads with validation.
- Add CI and automated browser tests.
- Add production logging and backup routine.
