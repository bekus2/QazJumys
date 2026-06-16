# PROJECT_CONTEXT.md

Project: QazJumys
Version: 1.2.0
Author: Beck Sarbassov
Last updated: 2026-06-16

## Goal

QazJumys is a Kazakhstan-focused freelance marketplace for digital services: SMM, ads, websites, SEO, CRM, 1C, design, video, and related work.

## Business Logic

A normal `member` account can act in both directions without switching roles:

- Publish a project as a customer.
- Browse open projects and submit proposals as a performer.
- Accept proposals on owned projects.
- Submit delivery when assigned.
- Complete work when the project owner accepts it.

The `owner` role is only for platform administration in `public/owner.php`.

## Main Workflow

1. Member publishes project with category, budget, skills, deadline, and brief.
2. Other members submit proposals.
3. Project owner accepts one proposal; project becomes `in_progress`.
4. Accepted performer can message, upload delivery files, and submit delivery.
5. Project owner reviews and completes the project.
6. Completion updates proposal status and performer counters.
7. Complaints can be submitted to owner moderation.

## Architecture

- Front controller: `public/index.php`.
- AJAX actions: `public/ajax.php`.
- Owner panel: `public/owner.php`.
- Protected downloads: `public/download.php`.
- Repositories isolate SQL logic.
- Views are plain PHP templates.
- Files are stored in `storage/uploads`, not in `public`.

## Data Flow

Browser forms include CSRF tokens. `ajax.php` validates token, session, account status, input, and business permissions. Repositories perform prepared SQL queries. Notifications are saved in `notifications`; optional emails are logged in `email_logs`.

## Security Logic

- Passwords are hashed.
- Sessions use HTTP-only cookies and SameSite=Lax.
- CSRF is required for POST actions.
- Blocked accounts cannot log in.
- Owner actions require `role=owner`.
- Uploads are MIME/size checked.
- Files download only through access-checked `download.php`.

## Owner Panel Logic

Owner can see detailed statistics, users, complaints, projects, email logs, and audit logs. Owner can block/unblock users, reset passwords, update complaint status, and change project status for moderation.

## Database

The schema includes `users`, `categories`, `projects`, `proposals`, `messages`, `project_files`, `complaints`, `notifications`, `email_logs`, and `audit_logs`.

## Limitations

Payments, escrow, full SMTP provider integration, self-service password change, and review moderation are not complete yet. They are documented as future development tasks.

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-16
Авторские права: © Beck Sarbassov. Все права защищены.
