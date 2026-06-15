# PROJECT_CONTEXT.md

## Goal
Build the first usable version of a Kazakhstan-only freelance portal for focused digital services. The portal connects clients with freelancers in a small set of high-demand categories.

## Business Logic
The marketplace has two user roles:
- Client: publishes projects and receives proposals.
- Freelancer: browses open projects and sends proposals.

Each project belongs to one category, has a budget range, deadline, and open status. Each freelancer can submit only one proposal per project.

## Target Users
- Kazakhstan entrepreneurs and small businesses that need digital work.
- Freelancers and agencies offering SMM, ads, websites, SEO, CRM, 1C, UI/UX, graphic design, video, and motion work.

## Core Features
- Public Kazakh homepage.
- Ten fixed service categories.
- Client and freelancer registration.
- Secure login/logout.
- Client project publishing.
- Project list with category and keyword filters.
- Freelancer proposal form.
- Role-based dashboard.
- Profile editing.

## Technical Decisions
- Plain PHP 8.3 was chosen to keep hosting simple.
- MySQL stores users, categories, projects, and proposals.
- PDO prepared statements are used for SQL safety.
- The public web root is isolated in `public/`.
- AJAX is used for form submissions.
- The UI is responsive and uses a local jQuery asset.

## Architecture Overview
```text
Browser
  -> public/index.php for pages
  -> public/ajax.php for POST actions
  -> app/Core for security and helpers
  -> app/Repositories for SQL
  -> MySQL
```

## Data Flow
1. User opens a page through `public/index.php`.
2. The front controller loads config, session, categories, projects, and role-specific data.
3. Forms submit to `public/ajax.php` using jQuery AJAX.
4. AJAX endpoint validates CSRF, role, and input.
5. Repositories write or read MySQL using prepared statements.
6. JSON response returns success/error and optional redirect.

## Security Logic
- Passwords are hashed with `password_hash()`.
- Authentication uses PHP sessions with HttpOnly cookies and SameSite=Lax.
- CSRF tokens are required for state-changing AJAX actions.
- SQL uses prepared statements.
- User output is escaped through `e()`.
- `.env` is ignored by Git.

## Admin Panel Logic
Version 1.0.0 does not include an admin panel. Admin moderation should be added as a separate module with its own role, routes, CSRF checks, and audit logs.

## API Logic
Internal AJAX actions:
- `register`
- `login`
- `logout`
- `project_create`
- `proposal_create`
- `profile_update`

No public REST API is included in version 1.0.0.

## Storage Logic
MySQL is required for persistent data. JSON storage is not used.

## Important Limitations
- No email sending yet.
- No admin moderation yet.
- No private messaging yet.
- No file upload yet.
- No payment, escrow, or dispute logic yet.

## Development Direction
Next versions should add moderation, messages, notifications, project workflow states, search improvements, tests, and production monitoring.
