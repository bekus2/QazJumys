# PROJECT_CONTEXT.md

Project: QazJumys
Version: 1.5.0
Author: Beck Sarbassov
Last updated: 2026-07-05

## Goal

QazJumys is a marketplace for digital freelance work. It supports customers, performers, and owner moderation without forcing separate customer/performer accounts.

## Business Logic

- Any `member` can publish a project.
- Any other active `member` can save the project, submit a proposal, send proposal files, and later submit delivery when accepted.
- Project owners can shortlist, decline, accept, cancel, complete, message, upload brief files, create milestones, and review performers.
- Performers can withdraw active proposals, message the project owner through their proposal, upload proposal/delivery files, submit delivery, create portfolio items, request verification, and review customers after completion.
- `owner` can moderate users, complaints, verification requests, project statuses, email logs, and audit logs.

## v1.5 Security Decisions

- Login is throttled: 5 failures per email and 20 per IP within 15 minutes (`login_attempts` table, fail-open with logging if the table is unavailable).
- `require_active_account()` re-validates account status/role against the database on every state-changing action; blocked users are logged out immediately.
- No credentials in the repository: `seed.sql` seeds categories only, the owner is created via `bin/create_owner.php`.
- Temporary passwords from owner resets are never stored in notification/email bodies.
- All pages send CSP, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, and `Referrer-Policy`.
- Secure cookies are detected behind proxies via `X-Forwarded-Proto`.
- Search input escapes LIKE wildcards.
- Errors are logged to `storage/logs/app-YYYY-MM-DD.log`; visitors never see exception details with `APP_DEBUG=false`.
- Project completion no longer inflates ratings; ratings come only from real reviews via `EngagementRepository::recalculateRating()`.

## v1.4 Security Decisions

- `acceptProposal()` only accepts `sent` or `shortlisted` proposals while the project is still `open`.
- Projects already accepted, submitted, completed, or cancelled cannot accept another proposal.
- `declined` and `withdrawn` proposals do not grant participant access.
- `canMessage()` checks the exact project-owner/proposal-performer pair.
- File access is separated:
  - `brief`: project owner, accepted performer, or active proposal participant.
  - `proposal`: project owner and the proposal author while proposal is active.
  - `delivery`: project owner and assigned performer.
- `download.php` delegates access decisions to `FileRepository::canAccess()`.
- `public/index.php` and `.htaccess` return 404 for private URL prefixes such as `.env`, `app`, `database`, `storage`, `logs`, and `backups`.

## Data Flow

Browser forms send CSRF-protected POST requests to `public/ajax.php`. Repositories validate workflow and ownership rules, then write through prepared PDO statements. Files are stored in `storage/uploads` and served only after permission checks.

## Database

The schema uses `utf8mb4_unicode_ci` and includes users, categories, projects, proposals, messages, project files, complaints, notifications, email logs, audit logs, saved projects/searches, milestones, reviews, portfolio items, verification requests, and login attempts (throttling).

## Owner Panel

`public/owner.php` shows platform statistics, user controls, complaints, project statuses, verification requests, email logs, and audit logs. Owner can block/unblock members, reset member passwords, update complaints, and approve/reject verification.

## Tests

- `tests/run.php` - CI integrity and syntax.
- `tests/db_smoke.php` - DB/install smoke (also runs in GitHub Actions against MariaDB).
- `tests/http_smoke.php` - local HTTP and direct-access probes.
- `tests/workflow_smoke.php` - project lifecycle smoke (also runs in GitHub Actions against MariaDB).

## Limitations

Payment/escrow, external SMTP provider integration, registration email verification, self-service password reset, and advanced dispute evidence are future work.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-07-05
Copyright: © Beck Sarbassov. All rights reserved.
