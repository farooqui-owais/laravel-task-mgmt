# Database Schema

## Entity Relationship Overview

```
users
  │
  ├─── tasks (assigned_to → users.id)
  │         (created_by  → users.id)
  │              │
  │              └─── task_histories (task_id → tasks.id)
  │                                  (changed_by → users.id)
  │
  └─── (Spatie) model_has_roles → roles → role_has_permissions → permissions
```

---

## Table: `users`

| Column             | Type         | Constraints              |
|--------------------|--------------|--------------------------|
| id                 | BIGINT       | PK, AUTO_INCREMENT       |
| name               | VARCHAR(255) | NOT NULL                 |
| email              | VARCHAR(255) | NOT NULL, UNIQUE         |
| email_verified_at  | TIMESTAMP    | NULLABLE                 |
| password           | VARCHAR(255) | NOT NULL (hashed)        |
| remember_token     | VARCHAR(100) | NULLABLE                 |
| created_at         | TIMESTAMP    |                          |
| updated_at         | TIMESTAMP    |                          |

---

## Table: `tasks`

| Column      | Type                                   | Constraints                      |
|-------------|----------------------------------------|----------------------------------|
| id          | BIGINT                                 | PK, AUTO_INCREMENT               |
| title       | VARCHAR(255)                           | NOT NULL                         |
| description | TEXT                                   | NULLABLE                         |
| priority    | ENUM('low','medium','high')            | NOT NULL, DEFAULT 'medium'       |
| status      | ENUM('pending','in-progress','completed') | NOT NULL, DEFAULT 'pending'   |
| due_date    | DATETIME                               | NULLABLE                         |
| assigned_to | BIGINT                                 | FK → users.id, NULL ON DELETE    |
| created_by  | BIGINT                                 | FK → users.id, NULL ON DELETE    |
| created_at  | TIMESTAMP                              |                                  |
| updated_at  | TIMESTAMP                              |                                  |

**Indexes:** `assigned_to`, `status`, `priority`, `due_date`

---

## Table: `task_histories`

| Column        | Type         | Constraints                   |
|---------------|--------------|-------------------------------|
| id            | BIGINT       | PK, AUTO_INCREMENT            |
| task_id       | BIGINT       | FK → tasks.id, CASCADE DELETE |
| changed_by    | BIGINT       | FK → users.id, NULL ON DELETE |
| field_changed | VARCHAR(255) | NOT NULL (e.g. 'status', 'assigned_to') |
| old_value     | VARCHAR(255) | NULLABLE                      |
| new_value     | VARCHAR(255) | NULLABLE                      |
| created_at    | TIMESTAMP    | AUTO (no updated_at)          |

**Indexes:** `task_id`, `changed_by`

---

## Tables: Spatie Permission (auto-managed)

| Table                    | Purpose                              |
|--------------------------|--------------------------------------|
| `roles`                  | Defines roles (admin, manager, employee) |
| `permissions`            | Granular permission strings          |
| `model_has_roles`        | Polymorphic pivot: user ↔ role       |
| `model_has_permissions`  | Polymorphic pivot: user ↔ permission |
| `role_has_permissions`   | Pivot: role ↔ permission             |

---

## Tables: Laravel Sanctum

| Table                   | Purpose                   |
|-------------------------|---------------------------|
| `personal_access_tokens`| Stores API bearer tokens  |

---

## Tables: Laravel Queues

| Table         | Purpose                          |
|---------------|----------------------------------|
| `jobs`        | Pending queued jobs              |
| `job_batches` | Batch job tracking               |
| `failed_jobs` | Jobs that failed after retries   |

---

## Seed Credentials (development only)

| Role     | Email                  | Password      |
|----------|------------------------|---------------|
| Admin    | admin@example.com      | Password123!  |
| Manager  | manager@example.com    | Password123!  |
| Employee | employee@example.com   | Password123!  |
