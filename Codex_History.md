# Codex_History.md

## 2026-06-16 — v1.0.0

### Краткое
Создана первая рабочая версия казахстанского фриланс-портала QazJumys с публичным интерфейсом на казахском языке, регистрацией, входом, ролями заказчика и исполнителя, проектами, откликами и личным кабинетом.

### Измененные файлы
- `.env.example`
- `.gitignore`
- `.htaccess`
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`
- `app/bootstrap.php`
- `app/Config/config.php`
- `app/Core/Auth.php`
- `app/Core/Csrf.php`
- `app/Core/Database.php`
- `app/Core/Response.php`
- `app/Core/Validator.php`
- `app/Repositories/CategoryRepository.php`
- `app/Repositories/ProjectRepository.php`
- `app/Repositories/UserRepository.php`
- `app/Views/layout/header.php`
- `app/Views/layout/footer.php`
- `app/Views/home.php`
- `app/Views/auth-register.php`
- `app/Views/auth-login.php`
- `app/Views/projects.php`
- `app/Views/project-create.php`
- `app/Views/dashboard.php`
- `app/Views/profile.php`
- `database/schema.sql`
- `database/seed.sql`
- `public/.htaccess`
- `public/index.php`
- `public/ajax.php`
- `public/assets/css/style.css`
- `public/assets/js/app.js`
- `public/assets/img/hero-marketplace.png`
- `public/assets/vendor/jquery-3.7.1.min.js`
- `storage/cache/.gitkeep`
- `storage/logs/.gitkeep`

### Добавлено
- Адаптивная главная страница на казахском языке.
- 10 категорий digital-услуг.
- Регистрация и вход для двух ролей.
- Публикация проектов заказчиком.
- Поиск и просмотр открытых проектов.
- Отправка отклика исполнителем.
- Ролевой кабинет.
- Редактирование профиля.
- MySQL schema и seed.
- jQuery/AJAX обработка форм.
- CSRF-защищенный выход из аккаунта через POST.

### Исправлено
- Не применимо, это первая версия.

### Безопасность
- Добавлены `password_hash()`, сессии с HttpOnly cookie, CSRF-токены, PDO prepared statements, серверная валидация, экранирование вывода и POST-only logout.
- `.env` исключен из Git.

### Примечания
- Нужно добавить админ-панель, email-уведомления, сообщения, загрузку файлов, rate limiting и автоматические тесты в следующих версиях.
