# HANDOFF.md

## Project
QazJumys is a Kazakhstan-focused freelance portal for digital services. The public UI is in Kazakh. Version 1.0.0 includes registration/login, two roles, project publishing, project browsing, proposal sending, dashboards, and profile editing.

## Technologies
- PHP 8.3
- MySQL or MariaDB
- HTML5, CSS3
- JavaScript, jQuery, AJAX
- PDO prepared statements

## Structure
- `public/index.php` is the front controller.
- `public/ajax.php` handles POST AJAX actions.
- `app/bootstrap.php` loads env, sessions, autoloading, and helpers.
- `app/Core/` contains DB, auth, CSRF, validation, and response utilities.
- `app/Repositories/` contains SQL data access.
- `app/Views/` contains PHP templates.
- `database/schema.sql` and `database/seed.sql` create and seed the database.

## Important Files
- `.env.example`
- `app/bootstrap.php`
- `app/Core/Auth.php`
- `app/Core/Csrf.php`
- `app/Repositories/ProjectRepository.php`
- `public/assets/css/style.css`
- `public/assets/js/app.js`
- `database/schema.sql`
- `database/seed.sql`

## Install and Run
1. Copy `.env.example` to `.env`.
2. Configure MySQL credentials.
3. Import `database/schema.sql`.
4. Import `database/seed.sql`.
5. Run `php -S 127.0.0.1:8080 -t public`.

## Deployment
Set the web server document root to `public/`. Keep `.env`, `app/`, `database/`, and `storage/` outside direct public access. Set `APP_DEBUG=false` in production and use HTTPS.

## Configuration
Main configuration is loaded from `.env` by `app/Config/config.php`.

## Do Not Change Without Understanding
- Session and CSRF logic in `app/bootstrap.php` and `app/Core/Csrf.php`.
- Role checks in `public/ajax.php`.
- SQL relationships in `database/schema.sql`.
- The public root separation between `public/` and private folders.

## Unfinished Tasks
- Admin moderation panel is not included.
- Email notifications are not implemented.
- Direct client-freelancer messaging is not implemented.
- File uploads are not implemented.
- Payment or escrow logic is not implemented.

## Future Improvements
- Add admin dashboard and audit logs.
- Add messaging, notifications, and project milestones.
- Add rate limiting for auth and proposal forms.
- Add richer search and category landing pages.
- Add automated tests and CI.
