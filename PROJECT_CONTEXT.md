# PROJECT_CONTEXT.md

Project: QazJumys
Version: 1.3.0
Author: Beck Sarbassov
Last updated: 2026-06-21

## Goal

QazJumys is a Kazakhstan-focused freelance marketplace for digital services: SMM, ads, websites, SEO, CRM, 1C, design, video, and related work.

## Business Logic

A normal `member` account can act in both directions without switching roles:

- Publish projects as a customer.
- Browse open projects and submit proposals as a performer.
- Save projects and reusable searches.
- Shortlist, accept, decline, or cancel work on owned projects.
- Withdraw own proposals before acceptance.
- Use milestones to track work.
- Submit delivery when assigned.
- Complete work when the project owner accepts it.
- Leave reviews after completed projects.
- Maintain portfolio and request verification.

The `owner` role is only for platform administration in `public/owner.php`.

## Main Workflow

1. Member publishes project with category, budget, skills, deadline, and brief.
2. Other members save/search projects and submit proposals.
3. Project owner shortlists or declines proposals.
4. Project owner accepts one proposal; project becomes `in_progress`.
5. Participants can message, upload files, and work through milestones.
6. Accepted performer submits delivery.
7. Project owner reviews and completes the project.
8. Both sides can leave reviews.
9. Complaints and verification requests can be reviewed by owner.

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
- Verification and owner actions are audited where needed.

## Owner Panel Logic

Owner can see detailed statistics, users, complaints, projects, verification requests, email logs, and audit logs. Owner can block/unblock users, reset passwords, approve/reject verification, update complaint status, and change project status for moderation.

## Database

The schema includes `users`, `categories`, `projects`, `proposals`, `messages`, `project_files`, `complaints`, `notifications`, `email_logs`, `audit_logs`, `saved_projects`, `saved_searches`, `project_milestones`, `reviews`, `portfolio_items`, and `verification_requests`.

## Limitations

Payments, escrow, full SMTP provider API integration, and advanced dispute evidence workflow are not complete yet. They are future development tasks.

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-21
Авторские права: © Beck Sarbassov. Все права защищены.
