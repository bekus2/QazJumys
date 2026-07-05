# TASK.md

Project: QazJumys
Current version: 1.5.0
Author: Beck Sarbassov
Last updated: 2026-07-05

## Objective

Deliver a working PHP freelance marketplace with secure unified accounts, complete project/proposal workflow, messaging, protected file uploads/downloads, reviews, portfolio, verification, complaints, owner tools, and automated checks.

## Required Functionality

- Public homepage and searchable projects.
- Member registration/login.
- Unified member account for both publishing and performing.
- Project create/search/save/cancel/complete.
- Proposal create/shortlist/withdraw/decline/accept.
- Strict acceptance: only active proposals on open projects can be accepted.
- Messaging only between valid project/proposal participants.
- Protected uploads split into `brief`, `proposal`, `delivery`.
- Downloads only through `download.php`.
- Saved searches, milestones, reviews, portfolio, verification requests.
- Owner panel for statistics, complaints, verification, user block/unblock, password reset, project moderation, email logs, audit logs.

## Pages And Modules

- `index.php?page=home`
- `index.php?page=projects`
- `index.php?page=register`
- `index.php?page=login`
- `index.php?page=project-create`
- `index.php?page=dashboard`
- `index.php?page=profile`
- `owner.php`
- `download.php`

## Security Requirements

- Keep `.env` out of Git and out of public web access.
- Do not publish real passwords, password hashes, or owner credentials in public docs or seed files.
- Create the owner account only via `php bin/create_owner.php`.
- Keep login rate limiting active (`login_attempts` table must exist).
- Re-validate account status in the database on state-changing actions.
- Use `APP_DEBUG=false` on staging/production.
- Keep `MAIL_ENABLED=false` until mail is verified.
- Require HTTPS in production.
- Keep baseline security headers (CSP, X-Frame-Options, nosniff, Referrer-Policy).
- Protect `storage/uploads` from direct browsing.
- Validate upload MIME/size and visibility permission before storing.
- Use CSRF on state-changing actions.
- Use PDO prepared statements and escape LIKE wildcards in search input.

## Installation Acceptance Criteria

- `.env` created from `.env.example`.
- `APP_URL` matches the actual local launch path.
- Database `qazjumys_portal` exists.
- `schema.sql`, `seed.sql`, and local `demo.sql` import successfully (plus `upgrade_1_5_0.sql` for upgrades).
- Owner account created via `php bin/create_owner.php`.
- Database collation is `utf8mb4_unicode_ci`.
- PHP extensions `pdo_mysql`, `mbstring`, `fileinfo`, `session` are enabled.
- Document root is `public`, or private folders are blocked by server rules.
- `storage/uploads`, `storage/logs`, `storage/cache` are writable.

## Workflow Acceptance Criteria

- Owner can open `owner.php`.
- A client member can publish a project.
- A performer member can find/save a project and submit a proposal.
- Client can shortlist and accept the proposal.
- Repeat accept and withdrawn/declined accept are blocked.
- Client and performer can exchange messages.
- Client can upload brief files.
- Performer can upload proposal and delivery files.
- Performer can submit delivery.
- Client can complete the project.
- Both sides can leave reviews.
- Performer can create portfolio and request verification.
- Owner can approve/reject verification.
- Member can create complaint.
- Owner can update complaint, block/unblock a test user, and reset a test user password.

## Test Commands

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\run.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\db_smoke.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\workflow_smoke.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\http_smoke.php http://127.0.0.1:8014
```

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-07-05
Copyright: © Beck Sarbassov. All rights reserved.
