# HANDOFF.md

Project: QazJumys
Version: 1.5.0
Author: Beck Sarbassov
Last updated: 2026-07-05

## What This Project Is

QazJumys is a plain PHP freelance marketplace. A normal member account can publish projects and also submit proposals to other projects. The owner panel is located at `public/owner.php`.

## Technologies

- PHP 8.1+; local OpenServer currently uses `C:\OSPanel\modules\PHP-8.4\php.exe`.
- MariaDB/MySQL; local OpenServer currently uses `C:\OSPanel\modules\MariaDB-10.4\bin\mysql.exe`.
- PDO, sessions, CSRF tokens, plain PHP views, CSS, JavaScript/jQuery.
- MySQL/MariaDB charset/collation: `utf8mb4` / `utf8mb4_unicode_ci`.

## Important Files

- `public/index.php` - public front controller.
- `public/ajax.php` - POST actions with CSRF, rate limiting, and permission checks.
- `public/owner.php` - owner operations panel.
- `public/download.php` - protected file download endpoint.
- `bin/create_owner.php` - CLI owner installer (no credentials in the repo).
- `app/Core/RateLimiter.php` - login brute-force throttling (`login_attempts` table).
- `app/bootstrap.php` - config, sessions, security headers, `app_log()` error logging.
- `app/Repositories/ProjectRepository.php` - project/proposal workflow and messaging permissions.
- `app/Repositories/FileRepository.php` - upload metadata plus `brief/proposal/delivery` access rules.
- `app/Repositories/EngagementRepository.php` - saved items, milestones, reviews, portfolio, verification.
- `database/schema.sql`, `seed.sql`, `demo.sql` - database setup.
- `tests/run.php`, `db_smoke.php`, `http_smoke.php`, `workflow_smoke.php` - automated checks.

## Install And Run

1. Copy `.env.example` to `.env`.
2. For this OpenServer machine, use `APP_URL=http://127.0.0.1:8014` and `DB_HOST=127.0.1.14`.
3. Import `database/schema.sql`, then `database/seed.sql`, then `database/demo.sql` only for local testing (upgrades from v1.4.0 also need `database/upgrade_1_5_0.sql`).
4. Create the owner account: `php bin/create_owner.php`.
5. Run from the project root:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" -S 127.0.0.1:8014 -t public
```

## Verification Commands

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\run.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\db_smoke.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\workflow_smoke.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\http_smoke.php http://127.0.0.1:8014
```

## Do Not Change Casually

- `ProjectRepository::acceptProposal()`, `isParticipant()`, and `canMessage()`.
- `FileRepository::canUpload()` and `canAccess()`.
- `public/ajax.php` CSRF/session checks, `require_active_account()` DB status re-check, and login rate limiting.
- `public/download.php` access checks.
- `app/bootstrap.php` security headers and secure-cookie detection.
- `.env` secret handling and upload storage path.
- Never re-add credential hashes or personal contacts to seed/config/docs.

## Deployment Notes

Use `public` as document root. If that is impossible, block direct web access to `.env`, `app`, `database`, `storage`, logs, backups, and SQL files. Keep uploads private and serve them only through `download.php`.

## Remaining Improvements

- Payment/escrow.
- External SMTP provider and registration email verification.
- Self-service password reset by email.
- Rich dispute evidence workflow.
- Owner table search/pagination.
- Full-text search via `MATCH ... AGAINST`.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-07-05
Copyright: © Beck Sarbassov. All rights reserved.
