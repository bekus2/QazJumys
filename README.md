# QazJumys

## English

### Project Description
QazJumys is a Kazakhstan-focused freelance portal for digital work. Version 1.0.0 supports public browsing, client and freelancer registration, login, project publishing, project search, freelancer proposals, personal dashboards, and profile editing.

The public interface is in Kazakh. Project documentation is maintained in English and Russian for handoff and GitHub collaboration.

### System Requirements
- PHP 8.3 or newer
- MySQL 8.0 or MariaDB 10.6 or newer
- Apache or Nginx with the document root pointed to `public/`
- PHP extensions: `pdo`, `pdo_mysql`, `mbstring`, `session`, `json`
- Modern browser with JavaScript enabled

### Installation
1. Clone the repository.
2. Copy `.env.example` to `.env`.
3. Update `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.
4. Import `database/schema.sql`.
5. Import `database/seed.sql`.
6. Set the web server document root to the `public/` directory.

### Local Run
```bash
php -S 127.0.0.1:8080 -t public
```

Open:
```text
http://127.0.0.1:8080
```

### Usage
- Guests can view the homepage, categories, and open projects.
- Clients can register, log in, publish projects, and view proposals counters.
- Freelancers can register, log in, browse open projects, send proposals, and track sent proposals.
- Authenticated users can update profile name, city, bio, and skills.

### Folder and File Structure
```text
app/
  Config/          Application configuration
  Core/            Database, auth, CSRF, validation, response helpers
  Repositories/    MySQL data access layer
  Views/           PHP templates
database/
  schema.sql       Database schema
  seed.sql         Initial service categories
public/
  assets/          CSS, JavaScript, vendor files, images
  ajax.php         AJAX endpoint
  index.php        Front controller
storage/
  cache/           Reserved cache folder
  logs/            Reserved log folder
```

### Backend
The backend is plain PHP 8.3 with PDO. It uses prepared statements, password hashing, session authentication, CSRF tokens, role checks, and server-side validation.

### Frontend
The frontend uses HTML5, CSS3, JavaScript, jQuery, and AJAX. It is responsive for mobile, tablet, laptop, and desktop screens.

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

The first version includes 10 service categories:
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

### Forms and Notifications
Version 1.0.0 includes backend form handling for registration, login, project publishing, proposal sending, and profile editing. Email notification sending is not implemented yet. Default notification email for future integration: `bek0435@gmail.com`.

### Authorization and Authentication
Authentication is session-based. Passwords are hashed with `password_hash()`. Client-only actions and freelancer-only actions are separated by role checks.

### Admin Panel
Version 1.0.0 does not include an admin panel. No default admin account is active in the application. If an admin panel is added later, default local credentials must be documented only in this README and changed before production deployment.

### Security Notes
- Keep `.env` out of Git.
- Change all local credentials before production.
- Use HTTPS in production.
- Keep `app/`, `database/`, and `storage/` outside public document access.
- CSRF protection is enabled for state-changing AJAX requests.
- User input is validated and escaped before display.

### Deployment Notes
- Point the domain document root to `public/`.
- Import schema and seed files before the first run.
- Ensure PHP can read project files and write to `storage/logs` if logging is later enabled.
- Disable debug mode in production by setting `APP_DEBUG=false`.

### Scaling Recommendations
- Add admin moderation for users, projects, and proposals.
- Add messaging between clients and freelancers.
- Add project status workflow and contract milestones.
- Add file uploads with validation and private storage.
- Add email notifications and rate limiting.
- Add search relevance with MySQL full-text queries or a dedicated search service.

## Русский

### Описание проекта
QazJumys - фриланс-портал для Казахстана, сфокусированный на digital-задачах. Версия 1.0.0 поддерживает публичный просмотр, регистрацию заказчика и исполнителя, вход, публикацию проектов, поиск проектов, отклики исполнителей, личные кабинеты и редактирование профиля.

Публичный интерфейс выполнен на казахском языке. Документация ведется на английском и русском языках для передачи проекта и работы через GitHub.

### Системные требования
- PHP 8.3 или новее
- MySQL 8.0 или MariaDB 10.6 или новее
- Apache или Nginx с document root на `public/`
- Расширения PHP: `pdo`, `pdo_mysql`, `mbstring`, `session`, `json`
- Современный браузер с включенным JavaScript

### Установка
1. Клонируйте репозиторий.
2. Скопируйте `.env.example` в `.env`.
3. Настройте `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
4. Импортируйте `database/schema.sql`.
5. Импортируйте `database/seed.sql`.
6. Настройте web server document root на папку `public/`.

### Локальный запуск
```bash
php -S 127.0.0.1:8080 -t public
```

Откройте:
```text
http://127.0.0.1:8080
```

### Использование
- Гости могут смотреть главную страницу, категории и открытые проекты.
- Заказчики могут регистрироваться, входить, публиковать проекты и видеть счетчики откликов.
- Исполнители могут регистрироваться, входить, смотреть проекты, отправлять предложения и отслеживать свои отклики.
- Авторизованные пользователи могут редактировать имя, город, био и навыки.

### Структура папок и файлов
```text
app/
  Config/          Конфигурация приложения
  Core/            Database, auth, CSRF, validation, response helpers
  Repositories/    Слой доступа к MySQL
  Views/           PHP-шаблоны
database/
  schema.sql       Схема базы данных
  seed.sql         Начальные категории услуг
public/
  assets/          CSS, JavaScript, vendor-файлы, изображения
  ajax.php         AJAX endpoint
  index.php        Front controller
storage/
  cache/           Зарезервированная папка кеша
  logs/            Зарезервированная папка логов
```

### Backend
Backend написан на чистом PHP 8.3 с PDO. Используются подготовленные SQL-запросы, хеширование паролей, сессионная аутентификация, CSRF-токены, проверка ролей и серверная валидация.

### Frontend
Frontend использует HTML5, CSS3, JavaScript, jQuery и AJAX. Верстка адаптирована под мобильные телефоны, планшеты, ноутбуки и десктопы.

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

Первая версия содержит 10 категорий услуг:
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

### Формы и уведомления
Версия 1.0.0 содержит backend-обработчики форм регистрации, входа, публикации проекта, отправки отклика и редактирования профиля. Отправка email-уведомлений пока не реализована. Email по умолчанию для будущих уведомлений: `bek0435@gmail.com`.

### Авторизация и аутентификация
Аутентификация основана на сессиях. Пароли хешируются через `password_hash()`. Действия заказчика и исполнителя разделены проверками ролей.

### Панель администратора
В версии 1.0.0 панель администратора не включена. Активного администратора по умолчанию в приложении нет. Если панель администратора будет добавлена позже, начальные локальные учетные данные должны быть указаны только в этом README и изменены перед production-развертыванием.

### Примечания по безопасности
- Не добавляйте `.env` в Git.
- Измените все локальные учетные данные перед production.
- Используйте HTTPS в production.
- Папки `app/`, `database/`, `storage/` не должны быть доступны напрямую из web.
- CSRF-защита включена для AJAX-запросов, меняющих состояние.
- Пользовательский ввод проверяется и экранируется перед выводом.

### Примечания по развертыванию
- Укажите document root домена на `public/`.
- Импортируйте schema и seed перед первым запуском.
- Убедитесь, что PHP может читать файлы проекта и писать в `storage/logs`, если позже будет включено логирование.
- Отключите debug-режим в production через `APP_DEBUG=false`.

### Рекомендации по масштабированию
- Добавить админ-модерацию пользователей, проектов и откликов.
- Добавить сообщения между заказчиком и исполнителем.
- Добавить workflow статусов проекта и milestone-оплату.
- Добавить загрузку файлов с проверкой и приватным хранением.
- Добавить email-уведомления и rate limiting.
- Улучшить поиск через MySQL FULLTEXT или отдельный поисковый сервис.

Автор: Beck Sarbassov  
Дата создания: 2026-06-16  
Последнее обновление: 2026-06-16  
Авторские права: © Beck Sarbassov. Все права защищены.
