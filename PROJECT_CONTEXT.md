# PROJECT_CONTEXT.md

## Goal
Build a usable Kazakhstan-focused freelance marketplace for digital services. QazJumys connects clients with freelancers in focused categories such as SMM, advertising, websites, SEO, CRM, 1C, UI/UX, graphic design, video, and motion.

## Business Logic
The marketplace has two user roles:
- Client: publishes projects and receives proposals.
- Freelancer: browses open projects and sends proposals.

Each project belongs to one category and includes a title, description, project type, experience level, skills, location, remote flag, featured flag, urgent flag, budget range, deadline, and status. Each freelancer can submit only one proposal per project.

## Target Users
- Kazakhstan entrepreneurs and small businesses that need digital work.
- Freelancers and agencies offering digital services.
- Future admin/moderator role for quality control and dispute review.

## Core Features in v1.1.0
- Public Kazakh homepage with marketplace counters.
- Ten fixed service categories.
- Featured and urgent project presentation.
- Client and freelancer registration.
- Secure login/logout.
- Client project publishing with rich brief fields.
- Project list with keyword, category, type, level, budget, and sort filters.
- Freelancer proposal form.
- Role-based dashboard.
- Profile editing with headline, skills, city, and freelancer hourly rate.
- Production-safe category seed and optional local demo data.

## Technical Decisions
- Plain PHP was chosen to keep deployment simple on shared hosting.
- MySQL/MariaDB stores users, categories, projects, and proposals.
- PDO prepared statements are used for SQL safety.
- Public web root is isolated in `public/`.
- AJAX is used for state-changing forms.
- No frontend framework is used; CSS and JS are local project assets.
- `database/demo.sql` is separated from `database/seed.sql` to avoid production demo accounts.

## Architecture Overview
```text
Browser
  -> public/index.php for pages
  -> public/ajax.php for POST actions
  -> app/Core for security and helpers
  -> app/Repositories for SQL
  -> MySQL/MariaDB
```

## Data Flow
1. User opens a page through `public/index.php`.
2. The front controller loads config, session, categories, marketplace stats, projects, and role-specific data.
3. Forms submit to `public/ajax.php` using jQuery AJAX.
4. AJAX endpoint validates CSRF, role, and input.
5. Repositories write or read MySQL using prepared statements.
6. JSON response returns success/error and optional redirect.

## Marketplace Search Logic
Project search supports:
- Keyword query over title, description, and skills.
- Category filter.
- Project type: fixed or hourly.
- Experience level: entry, intermediate, expert.
- Minimum and maximum budget range.
- Sort by recommended/latest, high budget, low budget, closest deadline, or fewest proposals.

## Security Logic
- Passwords are hashed with `password_hash()`.
- Authentication uses PHP sessions with HttpOnly cookies and SameSite=Lax.
- CSRF tokens are required for state-changing AJAX actions.
- SQL uses prepared statements.
- User output is escaped through `e()`.
- `.env` is ignored by Git.
- `database/demo.sql` must not be imported into production unless demo accounts are immediately removed.
- SQL import must use `utf8mb4` to protect Kazakh/Russian text.

## Admin Panel Logic
Version 1.1.0 does not include an admin panel. Future admin work should be added as a separate module with its own role, routes, CSRF checks, moderation permissions, password-change flow, and audit logs.

## API Logic
Internal AJAX actions:
- `register`
- `login`
- `logout`
- `project_create`
- `proposal_create`
- `profile_update`

No public REST API is included in version 1.1.0.

## Storage Logic
MySQL/MariaDB is required for persistent data. JSON storage is not used for application data.

SQL files:
- `database/schema.sql` for new installs.
- `database/seed.sql` for production-safe categories.
- `database/demo.sql` for local preview data.
- `database/upgrade_1_1_0.sql` for upgrading a v1.0.0 database.

## Important Limitations
- No email sending yet.
- No admin moderation yet.
- No private messaging yet.
- No file upload yet.
- No payment, escrow, or dispute logic yet.
- No automated tests or CI yet.

## Development Direction
Next versions should add admin moderation, messaging, notifications, accepted-proposal workflow, project milestones, file uploads, rate limiting, tests, CI, backups, production logging, and search relevance improvements.
