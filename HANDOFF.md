# HANDOFF.md

Project: QazJumys
Version: 1.2.0
Author: Beck Sarbassov
Last updated: 2026-06-16

## What This Project Is

QazJumys is a plain PHP 8.1 freelance marketplace for digital work. It now supports unified member accounts, publishing projects, submitting/accepting proposals, completing work, participant messaging, protected uploads, complaints, notifications, and a protected owner panel at `public/owner.php`.

## Technologies

- PHP 8.1, PDO, sessions.
- MySQL/MariaDB with `utf8mb4`.
- HTML/CSS/JavaScript/jQuery.
- OpenServer for local Windows launch.
- GitHub Actions CI via `.github/workflows/ci.yml`.

## Important Files

- `public/index.php` - public front controller.
- `public/ajax.php` - CSRF-protected state-changing actions.
- `public/owner.php` - owner administration panel.
- `public/download.php` - protected file download endpoint.
- `app/Core/Auth.php`, `Csrf.php`, `Database.php`, `Upload.php` - security and infrastructure.
- `app/Repositories/*Repository.php` - database logic.
- `database/schema.sql`, `seed.sql`, `demo.sql`, `upgrade_1_2_0.sql` - database setup.
- `.env.example` - configuration template.

## Install and Run

1. Copy `.env.example` to `.env`.
2. Import `database/schema.sql`, then `database/seed.sql`.
3. Import `database/demo.sql` only for local demo data.
4. Open the project through OpenServer or a web server pointing to `public`.
5. Run checks with `C:\OSPanel\modules\php\PHP_8.1\php.exe tests\run.php`.

## Deployment

Use `public` as document root. Keep `app`, `database`, `storage`, `.env`, logs, and backups outside public access. Set production `.env`, create a `utf8mb4_unicode_ci` database, import SQL, and make `storage/uploads`, `storage/logs`, `storage/cache` writable.

## Do Not Change Casually

- Session/auth flow in `Auth.php`.
- CSRF validation in `ajax.php`.
- Upload validation and storage path in `Upload.php`.
- Workflow transitions in `ProjectRepository.php`.
- Owner-only checks in `owner.php` and owner AJAX handlers.

## Remaining Improvements

- Password self-change and forced reset screen.
- SMTP provider integration instead of PHP `mail()`.
- Payment/escrow module.
- Reviews and dispute evidence workflow.
- Pagination/search inside owner tables.

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-16
Авторские права: © Beck Sarbassov. Все права защищены.
