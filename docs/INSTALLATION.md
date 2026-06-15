# Installation & Deployment Guide

## Requirements

| Dependency | Minimum Version |
|------------|-----------------|
| PHP        | 8.2             |
| Composer   | 2.x             |
| MySQL      | 8.0+ (or PostgreSQL 14+) |
| Redis      | 6.x (optional – for Redis queue) |
| Node.js    | 18+ (only if adding a frontend) |

---

## 1. Clone & Install

```bash
git clone https://github.com/your-org/task-management.git
cd task-management

composer install --optimize-autoloader --no-dev   # production

# Ensure storage directories exist
mkdir -p storage/framework/{cache/data,sessions,views}
chmod -R 775 storage bootstrap/cache
# OR
composer install                                   # development
```

---

## 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set:

```dotenv
# Application
APP_NAME="Task Management System"
APP_ENV=production          # use "local" for development
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Queue driver: "database" (simple) or "redis" (recommended for production)
QUEUE_CONNECTION=database

# Mail (example using SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@yourdomain.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Task Management"

# Redis (only needed when QUEUE_CONNECTION=redis)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 3. Database Setup

```bash
# Create the database first (MySQL example)
mysql -u root -p -e "CREATE DATABASE task_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Generate cache table (if using database cache driver)
php artisan make:cache-table

# Run all migrations
php artisan migrate

# Seed roles, permissions, and sample users
php artisan db:seed
```

### Seed credentials (development only – change in production)

| Role     | Email                | Password     |
|----------|----------------------|--------------|
| Admin    | admin@example.com    | Password123! |
| Manager  | manager@example.com  | Password123! |
| Employee | employee@example.com | Password123! |

---

## 4. Queue Worker

Notifications (task assigned, status changed) are dispatched to a queue.
The worker must be running to process them.

### Database queue (simple)

```bash
php artisan queue:work --queue=notifications,default --tries=3
```

### Redis queue (production)

```bash
php artisan queue:work redis --queue=notifications,default --tries=3 --sleep=3
```

### Using Supervisor (recommended for production)

Create `/etc/supervisor/conf.d/task-management-worker.conf`:

```ini
[program:task-management-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/task-management/artisan queue:work --queue=notifications,default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/task-management-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start task-management-worker:*
```

---

## 5. Caching (Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Running Tests

```bash
# Using PHPUnit
./vendor/bin/phpunit

# Using Pest (recommended)
./vendor/bin/pest

# With coverage report
./vendor/bin/pest --coverage

# Run a specific suite
./vendor/bin/pest tests/Feature/Auth
./vendor/bin/pest tests/Feature/Task
./vendor/bin/pest tests/Feature/Role
```

Tests use an **in-memory SQLite** database (configured in `phpunit.xml`) and
the `sync` queue driver so no worker is needed during testing.

---

## 7. Local Development Server

```bash
php artisan serve
# Runs at http://localhost:8000

# Process queued jobs in the background
php artisan queue:work --queue=notifications,default
```

---

## 8. Verifying the Installation

```bash
# Check routes
php artisan route:list --path=api

# Quick smoke test (login)
curl -s -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Password123!"}' | python3 -m json.tool
```

---

## 9. Resetting / Fresh Install

```bash
php artisan migrate:fresh --seed
```

---

## 10. Architecture Notes

| Pattern            | Location                              |
|--------------------|---------------------------------------|
| Service Layer      | `app/Services/`                       |
| Repository Pattern | `app/Repositories/`                   |
| Interfaces         | `app/Repositories/Interfaces/`        |
| Policies (RBAC)    | `app/Policies/`                       |
| Events             | `app/Events/`                         |
| Listeners          | `app/Listeners/`                      |
| Notifications      | `app/Notifications/`                  |
| API Resources      | `app/Http/Resources/`                 |
| Form Requests      | `app/Http/Requests/`                  |
| Enums              | `app/Enums/`                          |
| Migrations         | `database/migrations/`                |
| Seeders            | `database/seeders/`                   |
| Factories          | `database/factories/`                 |
| Tests              | `tests/Feature/`                      |

All business logic lives in Services. Controllers only handle HTTP
concerns (request → service → resource → response). No logic in models
beyond relationships and casts.
