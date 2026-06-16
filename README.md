# QazJumys

## English

### Project Description
QazJumys is a Kazakhstan-focused freelance marketplace for digital work. Version 1.1.0 includes a stronger marketplace interface, searchable project listings, project filters, featured/urgent project flags, richer client project briefs, freelancer profile cards, dashboard previews, secure AJAX forms, and separate production-safe and demo SQL seed files.

The public interface is in Kazakh. Project documentation is maintained in English and Russian for handoff and GitHub collaboration.

### System Requirements
- PHP 8.1 or newer; PHP 8.3 is recommended for production.
- MySQL 8.0 or MariaDB 10.6 or newer.
- Apache or Nginx with the document root pointed to `public/` when possible.
- PHP extensions: `pdo`, `pdo_mysql`, `mbstring`, `session`, `json`.
- Modern browser with JavaScript enabled.

### Installation
1. Clone the repository.
2. Copy `.env.example` to `.env`.
3. Update `APP_URL`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.
4. Import `database/schema.sql`.
5. Import `database/seed.sql` for production-safe categories.
6. Optional local preview only: import `database/demo.sql` to add demo users, projects, and proposals.
7. Set the web server document root to `public/`. If hosting cannot point to `public/`, the root `.htaccess` routes requests into `public/`.

### Local Run With OpenServer on This Computer
OpenServer Panel is installed at `C:\OSPanel`.

Current local setup prepared for this project:
- Project path: `C:\Users\Beck-S\Documents\GitHub\Experimental-Project-002`
- OpenServer junction: `C:\OSPanel\domains\localhost\qazjumys`
- Local URL: `http://localhost:8080/qazjumys/`
- MariaDB: `127.0.0.1:3306`
- Database: `qazjumys_portal`
- Local PHP binary: `C:\OSPanel\modules\php\PHP_8.1\php.exe`
- MariaDB client: `C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe`

OpenServer import commands:
```bat
"C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot < database\schema.sql
"C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot < database\seed.sql
"C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot < database\demo.sql
```

Important: use `--default-character-set=utf8mb4` when importing SQL, otherwise Kazakh and Russian text can be corrupted.

Alternative quick run:
```bat
"C:\OSPanel\modules\php\PHP_8.1\php.exe" -S localhost:8090 -t public
```

### Usage
- Guests can browse the homepage, categories, open projects, featured projects, and freelancer previews.
- Clients can register, log in, publish rich project briefs, view proposal counters, and edit profiles.
- Freelancers can register, log in, filter open projects, send proposals, track sent proposals, and edit profile cards.

### Folder and File Structure
```text
app/
  Config/          Application configuration
  Core/            Database, auth, CSRF, validation, response helpers
  Repositories/    MySQL data access layer
  Views/           PHP templates
database/
  schema.sql       Full database schema for new installs
  seed.sql         Production-safe category seed
  demo.sql         Optional local demo users/projects/proposals
  upgrade_1_1_0.sql Upgrade script from v1.0.0 database shape
public/
  assets/          CSS, JavaScript, vendor files, images
  ajax.php         AJAX endpoint
  index.php        Front controller
storage/
  cache/           Reserved cache folder
  logs/            Reserved log folder
```

### Backend
The backend is plain PHP with PDO. It uses prepared statements, password hashing, session authentication, CSRF tokens, role checks, server-side validation, and a private application folder outside direct public access.

### Frontend
The frontend uses semantic HTML5, CSS3, JavaScript, jQuery, and AJAX. Version 1.1.0 adds a denser marketplace-style layout, mobile navigation, advanced filters, project chips, freelancer cards, and responsive desktop/mobile breakpoints.

### API and AJAX
`public/ajax.php` accepts POST requests with a CSRF token.

Supported actions:
- `register`
- `login`
- `logout`
- `project_create`
- `proposal_create`
- `profile_update`

### Database
MySQL tables:
- `users`
- `categories`
- `projects`
- `proposals`

Version 1.1.0 project fields include project type, experience level, skills, location, remote flag, featured flag, urgent flag, budget range, deadline, status, and proposal counters.

For a fresh install, import:
1. `database/schema.sql`
2. `database/seed.sql`
3. Optional local only: `database/demo.sql`

For an existing v1.0.0 database, back up the database first, then run `database/upgrade_1_1_0.sql` once and import `database/seed.sql`.

### Forms and Notifications
Version 1.1.0 includes backend form handling for registration, login, project publishing, proposal sending, and profile editing. Email notification sending is not implemented yet. Default notification email for future integration: `bek0435@gmail.com`.

### Authorization and Authentication
Authentication is session-based. Passwords are hashed with `password_hash()`. Client-only actions and freelancer-only actions are separated by role checks. State-changing AJAX requests require CSRF tokens.

### Admin Panel
Version 1.1.0 does not include an admin panel. No default admin account is active in the application.

Reserved local admin credentials for a future admin module:
- Login: `bek0435@gmail.com`
- Password: `0123456789+Aa`

Security note: these credentials are only reserved for a future local/initial admin module. Change them immediately after first login and before any production deployment. Do not display admin credentials on public pages.

### Security Notes
- Keep `.env` out of Git.
- Change all local/demo credentials before production.
- Do not import `database/demo.sql` into production unless demo accounts are removed immediately.
- Use HTTPS in production.
- Keep `app/`, `database/`, and `storage/` outside public document access.
- CSRF protection is enabled for state-changing AJAX requests.
- User input is validated and escaped before display.
- Use `--default-character-set=utf8mb4` when importing SQL.

### Deployment to Hosting
1. Create a MySQL/MariaDB database in the hosting control panel.
2. Create a database user and grant privileges only to this project database.
3. Upload all project files to the hosting account.
4. If the hosting panel supports it, set domain document root to `public/`.
5. If document root cannot be changed, upload the full project to the web root and keep the root `.htaccess` enabled.
6. Copy `.env.example` to `.env`.
7. Set production values:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.kz
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_user
   DB_PASSWORD=your_strong_password
   ```
8. Import `database/schema.sql`.
9. Import `database/seed.sql`.
10. Do not import `database/demo.sql` on production.
11. Ensure `storage/logs` and `storage/cache` are writable if future logging/cache features are enabled.
12. Enable HTTPS/SSL.
13. Test homepage, registration, login, project creation, project search, proposal creation, and profile editing.
14. Remove installation leftovers, database dumps, and temporary files from public hosting.

### Scaling Recommendations
- Add admin moderation for users, projects, proposals, and categories.
- Add direct messaging between clients and freelancers.
- Add project status workflow and contract milestones.
- Add private file uploads with validation and virus scanning.
- Add email notifications, rate limiting, and audit logs.
- Add automated tests and CI.
- Add full-text relevance tuning or a dedicated search service.

## Русский

### Описание проекта
QazJumys — фриланс-маркетплейс для digital-задач в Казахстане. Версия 1.1.0 включает более зрелый интерфейс маркетплейса, поиск проектов, фильтры, признаки featured/urgent, расширенный brief проекта, карточки исполнителей, кабинет по ролям, защищенные AJAX-формы и отдельные SQL-файлы для production seed и локальных демо-данных.

Публичный интерфейс выполнен на казахском языке. Документация ведется на английском и русском языках.

### Системные требования
- PHP 8.1 или новее; для production рекомендуется PHP 8.3.
- MySQL 8.0 или MariaDB 10.6 или новее.
- Apache или Nginx с document root на `public/`, если хостинг это поддерживает.
- PHP-расширения: `pdo`, `pdo_mysql`, `mbstring`, `session`, `json`.
- Современный браузер с включенным JavaScript.

### Установка
1. Клонируйте репозиторий.
2. Скопируйте `.env.example` в `.env`.
3. Настройте `APP_URL`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
4. Импортируйте `database/schema.sql`.
5. Импортируйте `database/seed.sql` для безопасных production-категорий.
6. Только для локальной проверки: импортируйте `database/demo.sql`.
7. Настройте document root сервера на `public/`. Если это невозможно, корневой `.htaccess` направляет запросы в `public/`.

### Локальный запуск в OpenServer на этом компьютере
OpenServer Panel установлен в `C:\OSPanel`.

Текущая локальная настройка:
- Путь проекта: `C:\Users\Beck-S\Documents\GitHub\Experimental-Project-002`
- Junction OpenServer: `C:\OSPanel\domains\localhost\qazjumys`
- Локальный URL: `http://localhost:8080/qazjumys/`
- MariaDB: `127.0.0.1:3306`
- База данных: `qazjumys_portal`
- PHP: `C:\OSPanel\modules\php\PHP_8.1\php.exe`
- MySQL client: `C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe`

Команды импорта для OpenServer:
```bat
"C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot < database\schema.sql
"C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot < database\seed.sql
"C:\OSPanel\modules\database\MariaDB-10.8-Win10\bin\mysql.exe" --default-character-set=utf8mb4 -uroot < database\demo.sql
```

Важно: при импорте SQL используйте `--default-character-set=utf8mb4`, иначе казахский и русский текст может испортиться.

Альтернативный быстрый запуск:
```bat
"C:\OSPanel\modules\php\PHP_8.1\php.exe" -S localhost:8090 -t public
```

### Использование
- Гости просматривают главную страницу, категории, открытые проекты, featured-проекты и карточки исполнителей.
- Заказчики регистрируются, входят в систему, публикуют проекты, смотрят счетчики откликов и редактируют профиль.
- Исполнители регистрируются, входят в систему, фильтруют проекты, отправляют предложения, отслеживают отклики и редактируют профиль.

### Структура папок и файлов
```text
app/
  Config/          Конфигурация приложения
  Core/            Database, auth, CSRF, validation, response helpers
  Repositories/    Слой доступа к MySQL
  Views/           PHP-шаблоны
database/
  schema.sql       Полная схема для новой установки
  seed.sql         Безопасные production-категории
  demo.sql         Локальные демо-пользователи, проекты и отклики
  upgrade_1_1_0.sql Скрипт обновления базы с v1.0.0
public/
  assets/          CSS, JavaScript, vendor-файлы, изображения
  ajax.php         AJAX endpoint
  index.php        Front controller
storage/
  cache/           Резервная папка кэша
  logs/            Резервная папка логов
```

### Backend
Backend написан на чистом PHP с PDO. Используются подготовленные SQL-запросы, хеширование паролей, сессии, CSRF-токены, проверки ролей, серверная валидация и разделение public/private файлов.

### Frontend
Frontend использует HTML5, CSS3, JavaScript, jQuery и AJAX. Версия 1.1.0 добавляет плотный marketplace layout, мобильную навигацию, расширенные фильтры, теги навыков, карточки исполнителей и адаптивные desktop/mobile breakpoints.

### API и AJAX
`public/ajax.php` принимает POST-запросы с CSRF-токеном.

Поддерживаемые действия:
- `register`
- `login`
- `logout`
- `project_create`
- `proposal_create`
- `profile_update`

### База данных
Таблицы MySQL:
- `users`
- `categories`
- `projects`
- `proposals`

Поля проекта в v1.1.0: тип оплаты, уровень опыта, навыки, локация, удаленный формат, featured, urgent, бюджет, срок, статус и счетчик откликов.

Для новой установки импортируйте:
1. `database/schema.sql`
2. `database/seed.sql`
3. Только локально при необходимости: `database/demo.sql`

Для базы v1.0.0 сначала сделайте backup, затем один раз выполните `database/upgrade_1_1_0.sql` и импортируйте `database/seed.sql`.

### Формы и уведомления
Версия 1.1.0 содержит backend-обработчики регистрации, входа, публикации проекта, отправки предложения и редактирования профиля. Email-уведомления пока не реализованы. Email по умолчанию для будущих уведомлений: `bek0435@gmail.com`.

### Авторизация и аутентификация
Аутентификация основана на сессиях. Пароли хешируются через `password_hash()`. Действия заказчика и исполнителя разделены проверками ролей. Все POST AJAX действия требуют CSRF-токен.

### Панель администратора
В версии 1.1.0 панель администратора не включена. Активного администратора по умолчанию в приложении нет.

Зарезервированные локальные учетные данные для будущего admin-модуля:
- Логин: `bek0435@gmail.com`
- Пароль: `0123456789+Aa`

Важно: эти учетные данные предназначены только для будущей локальной/начальной установки admin-модуля. Их нужно изменить сразу после первого входа и до production-развертывания. Не отображайте учетные данные администратора на публичных страницах.

### Безопасность
- Не добавляйте `.env` в Git.
- Меняйте локальные/демо-учетные данные перед production.
- Не импортируйте `database/demo.sql` в production, если демо-аккаунты не будут сразу удалены.
- Используйте HTTPS в production.
- Папки `app/`, `database/`, `storage/` не должны быть доступны напрямую из web.
- CSRF-защита включена для AJAX-запросов, меняющих состояние.
- Пользовательский ввод валидируется и экранируется перед выводом.
- Импортируйте SQL с `--default-character-set=utf8mb4`.

### Развертывание на хостинге
1. Создайте MySQL/MariaDB базу данных в панели хостинга.
2. Создайте пользователя БД и выдайте права только на эту базу.
3. Загрузите все файлы проекта на хостинг.
4. Если панель позволяет, укажите document root домена на `public/`.
5. Если document root изменить нельзя, загрузите проект в web root и оставьте включенным корневой `.htaccess`.
6. Скопируйте `.env.example` в `.env`.
7. Укажите production-настройки:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.kz
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_user
   DB_PASSWORD=your_strong_password
   ```
8. Импортируйте `database/schema.sql`.
9. Импортируйте `database/seed.sql`.
10. Не импортируйте `database/demo.sql` в production.
11. Проверьте права записи для `storage/logs` и `storage/cache`, если позже будут включены логи/кэш.
12. Включите HTTPS/SSL.
13. Проверьте главную, регистрацию, вход, публикацию проекта, поиск, отправку предложения и редактирование профиля.
14. Удалите временные файлы, SQL-дампы и служебные архивы из публичной зоны хостинга.

### Рекомендации по масштабированию
- Добавить admin-модерацию пользователей, проектов, откликов и категорий.
- Добавить личные сообщения между заказчиком и исполнителем.
- Добавить workflow статусов проекта и milestone-оплату.
- Добавить приватные загрузки файлов с валидацией и проверкой.
- Добавить email-уведомления, rate limiting и audit logs.
- Добавить автоматические тесты и CI.
- Улучшить релевантность поиска через FULLTEXT или отдельный search service.

Автор: Beck Sarbassov  
Дата создания: 2026-06-16  
Последнее обновление: 2026-06-16  
Авторские права: © Beck Sarbassov. Все права защищены.
