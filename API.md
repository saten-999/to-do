# Todo App API — A Beginner's Guide

This project is both a normal browser-based Todo app **and** a REST API that
exposes the same data as JSON. It's meant to help students see the difference
between the two side by side.

```
                Laravel Todo App
                       |
             ┌─────────┴─────────┐
             ↓                   ↓
        Web Application       REST API
             ↓                   ↓
          web.php              api.php
             ↓                   ↓
      Web Controller       API Controller
             ↓                   ↓
        Blade/HTML          JSON
             ↓                   ↓
          Browser       Postman / Client
```

## What is an API?

An **API** (Application Programming Interface) lets one program talk to
another. Instead of a human clicking buttons in a browser, a program (a
mobile app, a JavaScript frontend, Postman, another server, etc.) sends an
HTTP request and gets data back — usually as JSON — instead of an HTML page.

## What does REST mean?

**REST** (Representational State Transfer) is a set of conventions for
designing APIs:

- Each **resource** (here, a `task`) has its own URL, e.g. `/api/tasks/1`.
- The **HTTP method** describes the action (read, create, update, delete).
- Responses are typically **JSON** and use standard **HTTP status codes** to
  describe the result.

## Web routes vs. API routes

| | Web (`routes/web.php`) | API (`routes/api.php`) |
|---|---|---|
| Consumer | Browser | Postman, JS frontend, mobile app, other services |
| Controller | `App\Http\Controllers\TaskController` | `App\Http\Controllers\Api\TaskController` |
| Response | Blade view → HTML | JSON (via `TaskResource`) |
| URL prefix | `/tasks` | `/api/tasks` |

The two controllers are kept completely separate on purpose: the web
controller never returns JSON, and the API controller never returns a view.

## HTTP methods used by this API

| Method | Meaning |
|---|---|
| `GET` | Read data (list or single resource). No side effects. |
| `POST` | Create a new resource. |
| `PUT` | Replace/update a resource with a full payload. |
| `PATCH` | Partially update a resource. |
| `DELETE` | Remove a resource. |

## HTTP status codes used by this API

| Code | Meaning | When |
|---|---|---|
| `200 OK` | Success | Successful `GET`, `PUT`/`PATCH`, `DELETE` (delete returns a confirmation message) |
| `201 Created` | Resource created | Successful `POST` |
| `404 Not Found` | Resource doesn't exist | Task id not found |
| `422 Unprocessable Entity` | Validation failed | Missing/invalid fields |

## Request headers

When sending a request body (`POST`, `PUT`, `PATCH`), send JSON and tell the
server to expect JSON back:

```
Content-Type: application/json
Accept: application/json
```

## Task JSON shape

```json
{
    "id": 1,
    "title": "Learn Laravel",
    "description": "Learn Laravel API development",
    "status": "todo",
    "completed": false,
    "priority": "medium",
    "due_date": "2026-10-01",
    "completed_at": null,
    "is_overdue": false,
    "created_at": "2026-09-24T10:00:00+00:00",
    "updated_at": "2026-09-24T10:00:00+00:00"
}
```

`status` is one of `todo`, `in_progress`, `completed`. `completed` is a
simple boolean shortcut for `status === "completed"`.

## Endpoints

### List tasks

```
GET /api/tasks
```

Optional query params: `search`, `status`, `priority`, `due`, `sort`,
`direction`, `per_page`.

Response: `200 OK` with a paginated `data` array of tasks.

### Get one task

```
GET /api/tasks/1
```

Response: `200 OK` with the task, or `404 Not Found` if it doesn't exist.

### Create a task

```
POST /api/tasks
Content-Type: application/json
```

```json
{
    "title": "Learn APIs",
    "description": "Learn Laravel REST APIs",
    "status": "todo",
    "priority": "medium"
}
```

Response: `201 Created` with the new task, or `422 Unprocessable Entity` with
validation errors, e.g.:

```json
{
    "message": "The title field is required.",
    "errors": {
        "title": ["The title field is required."]
    }
}
```

### Update a task (full update)

```
PUT /api/tasks/1
Content-Type: application/json
```

```json
{
    "title": "Learn Laravel APIs",
    "description": "Learn REST API development",
    "status": "completed",
    "priority": "high"
}
```

Response: `200 OK` with the updated task.

### Update a task (partial update)

```
PATCH /api/tasks/1
Content-Type: application/json
```

Same body/response as `PUT` in this API.

### Delete a task

```
DELETE /api/tasks/1
```

Response: `200 OK` with `{"message": "Task deleted successfully."}`.

### Mark complete / reopen (convenience endpoints)

```
PATCH /api/tasks/1/complete
PATCH /api/tasks/1/reopen
```

Response: `200 OK` with the updated task.

## Testing with Postman

1. Open Postman and create a new request.
2. Set the method (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`) and URL, e.g.
   `http://localhost:8000/api/tasks`.
3. For `POST`/`PUT`/`PATCH`, go to the **Body** tab, choose **raw** → **JSON**,
   and paste the request JSON shown above.
4. Under **Headers**, make sure `Accept: application/json` is set (Postman
   sets `Content-Type: application/json` automatically when Body is raw/JSON).
5. Click **Send** and inspect the status code and JSON response.
