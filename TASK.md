# TASK.md

## Project Goal
Create and maintain QazJumys, a Kazakhstan-focused freelance marketplace for narrow digital specializations, with the public interface fully in Kazakh.

## Current Version
1.1.0

## Required Functionality
- Public homepage with marketplace positioning and counters.
- Ten visible service categories.
- Featured/urgent project presentation.
- Client registration.
- Freelancer registration.
- Login and logout.
- Client dashboard.
- Freelancer dashboard.
- Client project publishing with rich project brief.
- Project search by keyword, category, type, experience level, budget, and sort order.
- Freelancer proposal submission.
- Profile editing with headline, city, bio, skills, and freelancer hourly rate.
- Production-safe SQL seed and optional local demo SQL seed.

## Pages and Modules
- Homepage
- Registration
- Login
- Projects list and filters
- Project create page
- Dashboard
- Profile
- AJAX actions
- MySQL/MariaDB schema and seed files

## User Roles
- Guest
- Client
- Freelancer

## Admin Panel Requirements
Admin panel is not part of version 1.1.0. Future admin module must include secure login, password change, role separation, CSRF protection, moderation, audit logs, and protected routes.

Reserved local credentials for a future admin module:
- Login: `bek0435@gmail.com`
- Password: `0123456789+Aa`

These credentials are not active in v1.1.0 and must be changed before production use if an admin panel is later added.

## UI Requirements
- Kazakh language public UI.
- Responsive mobile, tablet, laptop, and desktop layout.
- Marketplace-style homepage with real project and freelancer previews when data exists.
- Clear category cards.
- Advanced project filter bar.
- Clear project rows with category, type, level, skills, budget, deadline, client, and proposal count.
- Clear role selection during registration.
- Role-specific dashboard.
- Accessible forms and focus states.

## Backend Requirements
- PHP 8.1+ compatible code.
- PDO and prepared statements.
- Session auth.
- Password hashing.
- CSRF validation.
- Server-side validation.
- Clean separation of public and private files.
- UTF-8/utf8mb4-safe database import and storage.

## API Requirements
Internal AJAX endpoint: `public/ajax.php`.

Actions:
- `register`
- `login`
- `logout`
- `project_create`
- `proposal_create`
- `profile_update`

## Data Storage Requirements
MySQL/MariaDB tables:
- `users`
- `categories`
- `projects`
- `proposals`

`projects` must support:
- category
- client
- title
- description
- project type
- experience level
- skills
- location
- remote flag
- featured flag
- urgent flag
- budget range
- deadline days
- status

SQL files:
- `database/schema.sql`
- `database/seed.sql`
- `database/demo.sql`
- `database/upgrade_1_1_0.sql`

## Form Requirements
- Registration form.
- Login form.
- Project creation form.
- Proposal form.
- Profile update form.
- AJAX success/error feedback.
- CSRF token on every state-changing action.
- Client-side budget range check plus server-side validation.

## Email and Notification Requirements
Email notifications are not implemented in version 1.1.0. Default future notification email: `bek0435@gmail.com`.

## SEO Requirements
- Semantic HTML.
- Unique page title and description.
- Open Graph title and description.
- Public content indexable.
- Clean public root configuration.

## Performance Requirements
- Local jQuery file.
- Optimized single CSS and JS app files.
- Responsive image loading for hero asset.
- No unnecessary frontend frameworks.
- Efficient SQL counters through subqueries rather than broad grouped `p.*` queries.

## Security Requirements
- Do not commit `.env`.
- Do not store plaintext production passwords.
- Use HTTPS in production.
- Escape output.
- Validate all incoming form data.
- Protect role-only actions.
- Do not import `database/demo.sql` into production unless demo users are removed immediately.
- Keep SQL dumps and temporary files out of public web access.

## Deployment Requirements
- Preferred document root: `public/`.
- Fallback shared-hosting route: root `.htaccess` forwards to `public/`.
- Import `database/schema.sql` and `database/seed.sql`.
- Configure `.env`.
- Disable debug in production.
- Use `--default-character-set=utf8mb4` for SQL imports.
- Enable HTTPS/SSL.
- Verify write permissions only where needed.

## Acceptance Criteria
- Site opens locally through OpenServer at `http://localhost:8080/qazjumys/`.
- Homepage shows marketplace hero, counters, categories, featured projects, and freelancer previews when demo data exists.
- Project page shows advanced filters and readable project rows.
- User can register as client.
- User can register as freelancer.
- User can log in.
- Client can publish a project with type, level, skills, budget, location, deadline, and flags.
- Freelancer can view projects and submit a proposal.
- Dashboard changes based on role.
- Profile editing saves headline, city, bio, skills, and freelancer hourly rate.
- Layout works on mobile and desktop without horizontal overflow.
- Required documentation exists and reflects the project state.
