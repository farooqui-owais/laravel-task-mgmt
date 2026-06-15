# Multi-Role Task Management & Reporting System

A production-ready Laravel 11 REST API implementing multi-role task management with audit trails, queued notifications, and comprehensive test coverage.

---

## Features

| Epic | Feature | Status |
|------|---------|--------|
| 1    | Laravel Sanctum authentication | ✅ |
| 1    | RBAC via Spatie Permission (Admin / Manager / Employee) | ✅ |
| 1    | Laravel Policies & Gates | ✅ |
| 2    | Task CRUD with full lifecycle | ✅ |
| 2    | Task history / audit tracking | ✅ |
| 3    | Versioned REST API (`/api/v1/...`) | ✅ |
| 3    | API Resources & standardized responses | ✅ |
| 3    | Form Request validation | ✅ |
| 4    | Migrations with FK constraints & indexes | ✅ |
| 5    | Audit trail via Events & Observers | ✅ |
| 6    | Queued email notifications (assignment + status change) | ✅ |
| 7    | Feature tests (PHPUnit / Pest) | ✅ |
| 8    | Service + Repository pattern, PSR-12, no hardcoded values | ✅ |

---

## Quick Start

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

See [`docs/INSTALLATION.md`](docs/INSTALLATION.md) for full setup including queue workers.

---

## API Endpoints

| Method | Endpoint                  | Role Required         |
|--------|---------------------------|-----------------------|
| POST   | `/api/v1/login`           | Public                |
| POST   | `/api/v1/logout`          | Any authenticated     |
| GET    | `/api/v1/me`              | Any authenticated     |
| GET    | `/api/v1/users`           | Admin                 |
| POST   | `/api/v1/users`           | Admin                 |
| PUT    | `/api/v1/users/{id}`      | Admin                 |
| DELETE | `/api/v1/users/{id}`      | Admin                 |
| GET    | `/api/v1/tasks`           | Admin / Manager / Employee (scoped) |
| POST   | `/api/v1/tasks`           | Admin / Manager       |
| GET    | `/api/v1/tasks/{id}`      | Admin / Manager / Employee (own) |
| PUT    | `/api/v1/tasks/{id}`      | Admin / Manager / Employee (status only) |
| DELETE | `/api/v1/tasks/{id}`      | Admin / Manager       |

Full documentation: [`docs/API.md`](docs/API.md)

---

## Running Tests

```bash
./vendor/bin/pest
# or
./vendor/bin/phpunit
```

Tests use an in-memory SQLite database and synchronous queue — no external services required.

---

## Project Structure

```
app/
├── Enums/                  # TaskStatus, TaskPriority
├── Events/                 # TaskAssigned, TaskStatusChanged
├── Http/
│   ├── Controllers/Api/V1/ # AuthController, TaskController, UserController
│   ├── Requests/           # Form Request validation per action
│   └── Resources/          # API Resources (Task, User, TaskHistory)
├── Listeners/              # Queue-backed notification dispatchers
├── Models/                 # User, Task, TaskHistory
├── Notifications/          # TaskAssignedNotification, TaskStatusChangedNotification
├── Observers/              # (available for extension)
├── Policies/               # TaskPolicy, UserPolicy
├── Providers/              # AppServiceProvider (bindings, events, policies)
├── Repositories/           # TaskRepository, UserRepository + Interfaces
└── Services/               # AuthService, TaskService, UserService
database/
├── factories/              # UserFactory, TaskFactory
├── migrations/             # All schema migrations
└── seeders/                # DatabaseSeeder, RolesAndPermissionsSeeder
tests/Feature/
├── Auth/                   # AuthenticationTest
├── Task/                   # TaskCrudTest, ApiResponseTest
└── Role/                   # RoleBasedAccessTest
docs/
├── API.md                  # Full endpoint reference
├── DATABASE.md             # Schema documentation
└── INSTALLATION.md         # Setup & deployment guide
```

---

## Tech Stack

- **PHP** 8.2+
- **Laravel** 11
- **Laravel Sanctum** – API token authentication
- **Spatie Laravel Permission** – Roles & permissions
- **MySQL / PostgreSQL** – Primary database
- **Redis or Database** – Queue driver for notifications
- **Pest / PHPUnit** – Testing
=======
# laravel-task-mgmt
Task management api in laravel
