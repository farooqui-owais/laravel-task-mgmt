# API Documentation

## Base URL

```
http://localhost:8000/api/v1
```

All responses are JSON. All protected endpoints require the `Authorization: Bearer {token}` header.

---

## Authentication

### POST `/login`

Authenticate and receive a Sanctum token.

**Request Body**
```json
{
  "email": "admin@example.com",
  "password": "Password123!",
  "device_name": "my-app"   // optional, defaults to "api"
}
```

**Success Response `200`**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "System Admin",
      "email": "admin@example.com",
      "roles": ["admin"],
      "created_at": "2024-01-01T00:00:00+00:00",
      "updated_at": "2024-01-01T00:00:00+00:00"
    },
    "token": "1|abc123..."
  }
}
```

**Error Response `422`**
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

### POST `/logout` 🔒

Revoke the current access token.

**Success Response `200`**
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null
}
```

---

### GET `/me` 🔒

Return the authenticated user's profile.

**Success Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "System Admin",
    "email": "admin@example.com",
    "roles": ["admin"],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

## Users 🔒 (Admin only)

### GET `/users`

List all users (paginated).

**Query Parameters**
| Param    | Type    | Description              |
|----------|---------|--------------------------|
| page     | integer | Page number (default: 1) |

**Success Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "System Admin",
        "email": "admin@example.com",
        "roles": ["admin"],
        "created_at": "...",
        "updated_at": "..."
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 3
    }
  }
}
```

---

### POST `/users`

Create a new user.

**Request Body**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "Password123!",
  "role": "employee"   // "admin" | "manager" | "employee"
}
```

**Success Response `201`**
```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": {
    "id": 4,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "roles": ["employee"],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

### PUT `/users/{id}`

Update an existing user.

**Request Body** (all fields optional)
```json
{
  "name": "Jane Smith",
  "email": "janesmith@example.com",
  "password": "NewPassword123!",
  "role": "manager"
}
```

**Success Response `200`**
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": { ... }
}
```

---

### DELETE `/users/{id}`

Delete a user. Cannot delete your own account.

**Success Response `204`** (no body)

---

## Tasks 🔒

### GET `/tasks`

List tasks. Admins and Managers see all tasks; Employees see only their assigned tasks.

**Query Parameters**
| Param        | Type    | Description                              |
|--------------|---------|------------------------------------------|
| status       | string  | Filter: `pending`, `in-progress`, `completed` |
| priority     | string  | Filter: `low`, `medium`, `high`          |
| assigned_to  | integer | Filter by user ID (Admin/Manager only)   |
| due_date     | date    | Filter by exact due date (YYYY-MM-DD)    |
| page         | integer | Page number                              |

**Success Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Fix login bug",
        "description": "The login page throws a 500 error.",
        "priority": { "value": "high", "label": "High" },
        "status":   { "value": "pending", "label": "Pending" },
        "due_date": "2024-02-01T00:00:00+00:00",
        "assigned_to": {
          "id": 3,
          "name": "John Employee",
          "email": "employee@example.com"
        },
        "created_by": { "id": 1, "name": "System Admin" },
        "created_at": "...",
        "updated_at": "..."
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

---

### POST `/tasks`

Create a task. **Admin and Manager only.**

**Request Body**
```json
{
  "title": "Fix login bug",
  "description": "Optional description",
  "priority": "high",
  "status": "pending",
  "due_date": "2024-02-01 00:00:00",
  "assigned_to": 3
}
```

| Field       | Type     | Required | Rules                                    |
|-------------|----------|----------|------------------------------------------|
| title       | string   | ✅       | max:255                                  |
| description | string   | ❌       | nullable                                 |
| priority    | string   | ✅       | `low`, `medium`, `high`                  |
| status      | string   | ❌       | `pending`, `in-progress`, `completed`    |
| due_date    | datetime | ❌       | must be in the future                    |
| assigned_to | integer  | ❌       | must be a valid user ID                  |

**Success Response `201`**

---

### GET `/tasks/{id}`

Retrieve a single task with full history.

**Success Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "title": "Fix login bug",
    "description": "...",
    "priority": { "value": "high", "label": "High" },
    "status":   { "value": "in-progress", "label": "In Progress" },
    "due_date": "...",
    "assigned_to": { ... },
    "created_by":  { ... },
    "histories": [
      {
        "id": 2,
        "field_changed": "status",
        "old_value": "pending",
        "new_value": "in-progress",
        "changed_by": { "id": 2, "name": "Project Manager" },
        "changed_at": "2024-01-15T10:30:00+00:00"
      }
    ],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

### PUT `/tasks/{id}`

Update a task.

- **Admin / Manager**: can update any field.
- **Employee**: can **only** update `status` on tasks assigned to them.

**Request Body (Admin/Manager)**
```json
{
  "title": "Updated title",
  "description": "Updated description",
  "priority": "medium",
  "status": "in-progress",
  "due_date": "2024-03-01 00:00:00",
  "assigned_to": 3
}
```

**Request Body (Employee)**
```json
{
  "status": "completed"
}
```

**Success Response `200`**

---

### DELETE `/tasks/{id}`

Delete a task. **Admin and Manager only.**

**Success Response `204`** (no body)

---

## Standard Error Responses

| HTTP Status | Meaning                        |
|-------------|--------------------------------|
| 401         | Unauthenticated (missing/invalid token) |
| 403         | Forbidden (insufficient role)  |
| 404         | Resource not found             |
| 422         | Validation failed              |
| 500         | Internal server error          |

**Error shape:**
```json
{
  "success": false,
  "message": "Human-readable error message",
  "errors": { }  // present on 422 only
}
```
