# PROJECT_CONTEXT.md

Project: QazJumys
Version: 1.4.0
Author: Beck Sarbassov
Last updated: 2026-06-28

## Goal

QazJumys is a marketplace for digital freelance work. It supports customers, performers, and owner moderation without forcing separate customer/performer accounts.

## Business Logic

- Any `member` can publish a project.
- Any other active `member` can save the project, submit a proposal, send proposal files, and later submit delivery when accepted.
- Project owners can shortlist, decline, accept, cancel, complete, message, upload brief files, create milestones, and review performers.
- Performers can withdraw active proposals, message the project owner through their proposal, upload proposal/delivery files, submit delivery, create portfolio items, request verification, and review customers after completion.
- `owner` can moderate users, complaints, verification requests, project statuses, email logs, and audit logs.

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

The schema uses `utf8mb4_unicode_ci` and includes users, categories, projects, proposals, messages, project files, complaints, notifications, email logs, audit logs, saved projects/searches, milestones, reviews, portfolio items, and verification requests.

## Owner Panel

`public/owner.php` shows platform statistics, user controls, complaints, project statuses, verification requests, email logs, and audit logs. Owner can block/unblock members, reset member passwords, update complaints, and approve/reject verification.

## Tests

- `tests/run.php` - CI integrity and syntax.
- `tests/db_smoke.php` - local DB/install smoke.
- `tests/http_smoke.php` - local HTTP and direct-access probes.
- `tests/workflow_smoke.php` - local project lifecycle smoke.

## Limitations

Payment/escrow, external SMTP provider integration, and advanced dispute evidence are future work.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-28
Copyright: © Beck Sarbassov. All rights reserved.
