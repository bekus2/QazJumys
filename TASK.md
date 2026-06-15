# TASK.md

## Project Goal
Create version 1.0.0 of QazJumys, a Kazakhstan-focused freelance portal for narrow digital specializations, with the public interface fully in Kazakh.

## Current Version
1.0.0

## Required Functionality
- Public homepage.
- Ten visible service categories.
- Client registration.
- Freelancer registration.
- Login and logout.
- Client dashboard.
- Freelancer dashboard.
- Client project publishing.
- Project search by keyword and category.
- Freelancer proposal submission.
- Profile editing.

## Pages and Modules
- Homepage
- Registration
- Login
- Projects list
- Project create page
- Dashboard
- Profile
- AJAX actions
- MySQL schema and seed

## User Roles
- Guest
- Client
- Freelancer

## Admin Panel Requirements
Admin panel is not part of version 1.0.0. Future admin module must include secure login, role separation, CSRF protection, moderation, and audit logs.

## UI Requirements
- Kazakh language public UI.
- Responsive mobile, tablet, laptop, desktop layout.
- Clear category cards.
- Clear role selection during registration.
- Role-specific dashboard.
- Accessible forms and focus states.

## Backend Requirements
- PHP 8.3.
- PDO and prepared statements.
- Session auth.
- Password hashing.
- CSRF validation.
- Server-side validation.
- Clean separation of public and private files.

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
MySQL tables:
- `users`
- `categories`
- `projects`
- `proposals`

Seed must include:
- SMM және контент
- Таргет / performance
- Сайт әзірлеу
- SEO / Google Ads
- Мобилограф / Reels
- Frontend / веб-әзірлеу
- CRM / автоматтандыру
- 1C әзірлеу
- UI/UX және графдизайн
- Видеомонтаж / motion

## Form Requirements
- Registration form.
- Login form.
- Project creation form.
- Proposal form.
- Profile update form.
- AJAX success/error feedback.
- CSRF token on every state-changing action.

## Email and Notification Requirements
Email notifications are not implemented in version 1.0.0. Default future notification email: `bek0435@gmail.com`.

## SEO Requirements
- Semantic HTML.
- Unique title and description.
- Open Graph title and description.
- Public content indexable.

## Performance Requirements
- Local jQuery file.
- Optimized single CSS and JS app files.
- Responsive image loading for hero asset.
- No unnecessary frontend frameworks.

## Security Requirements
- Do not commit `.env`.
- Do not store plaintext passwords.
- Use HTTPS in production.
- Escape output.
- Validate all incoming form data.
- Protect role-only actions.

## Deployment Requirements
- Document root must point to `public/`.
- Import SQL schema and seed before use.
- Configure `.env`.
- Disable debug in production.

## Acceptance Criteria
- Site opens locally from `public/index.php`.
- Homepage shows the 10 service categories.
- User can register as client.
- User can register as freelancer.
- User can log in.
- Client can publish a project.
- Freelancer can view projects and submit a proposal.
- Dashboard changes based on role.
- Layout works on mobile and desktop.
- Required documentation exists and reflects the project state.
