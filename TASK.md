# TASK.md

Project: QazJumys
Current version: 1.2.0
Author: Beck Sarbassov
Last updated: 2026-06-16

## Objective

Deliver a working PHP freelance marketplace with unified accounts, owner administration, messaging, uploads, notifications, complaints, and automated tests/CI.

## Required Functionality

- Public homepage and searchable project marketplace.
- Member registration/login.
- Unified account: one member can publish projects and submit proposals.
- Project creation and project dashboard.
- Proposal submission, acceptance, decline.
- Workflow: open, in progress, submitted, completed, cancelled.
- Participant messaging.
- Protected file upload/download.
- Complaint creation and owner moderation.
- Notification records and email logs.
- Owner panel at `public/owner.php`.
- Automated test runner and GitHub Actions CI.

## Pages and Modules

- Home: `index.php?page=home`.
- Projects: `index.php?page=projects`.
- Register/login.
- Dashboard.
- Profile.
- Project create.
- Owner panel: `owner.php`.
- Protected download: `download.php`.

## User Roles

- `member`: can publish projects and take jobs.
- `owner`: can manage platform operations and moderation.

## Admin / Owner Requirements

Owner panel must show statistics, users, project workflow, complaints, email logs, and audit logs. Owner can block/unblock accounts, reset passwords, update complaints, and moderate project statuses.

## Backend Requirements

- PHP 8.1.
- PDO prepared statements.
- CSRF protection.
- Session authentication.
- Server-side validation.
- Private storage for uploads.

## Database Requirements

Use MySQL/MariaDB with `utf8mb4_unicode_ci`. Required tables are defined in `database/schema.sql`. Use `database/upgrade_1_2_0.sql` for upgrading from v1.1.0.

## Email / Notifications

Notifications are stored in the database. Email attempts are logged in `email_logs`. Set `MAIL_ENABLED=true` only after the hosting mail setup is confirmed.

## SEO and Performance

Public pages use semantic HTML, title/description metadata, Open Graph basics, responsive CSS, and optimized local assets.

## Security Requirements

Protect `.env`, storage, logs, uploads, SQL dumps, and backups. Never expose secrets in public pages. Keep owner credentials only in private operational documentation/README and change them before production.

## Acceptance Criteria

- `tests/run.php` passes.
- Main pages return HTTP 200 locally.
- Owner can log in to `owner.php`.
- Member can publish project and submit proposal.
- Project owner can accept proposal and complete work.
- Performer can upload delivery and submit work.
- Owner can manage complaints and user status.

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-16
Авторские права: © Beck Sarbassov. Все права защищены.
