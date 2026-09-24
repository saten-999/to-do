# To-Do List

A simple, single-application To-Do List built with Laravel. There are no user accounts — all tasks
belong to the application globally.

## Features

- Create, view, edit and delete tasks
- Mark tasks as completed / reopen completed tasks
- Dashboard with task statistics (total, to do, in progress, completed, overdue)
- Search tasks by title/description
- Filter by status, priority and due date (overdue, due today, due this week, no due date)
- Sort by created date, due date, priority, title or status (ascending/descending)
- Responsive, accessible UI with clear status/priority indicators and delete confirmation

## Requirements

- PHP 8.3
- Composer
- MySQL

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Database configuration

Update the following values in `.env` to match your local MySQL setup (a database named `todo` is
used by default):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo
DB_USERNAME=root
DB_PASSWORD=
```

Create the database before migrating, for example:

```bash
mysql -u root -e "CREATE DATABASE todo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Migrations and seeding

Run the migrations and seed at least 20 sample tasks (a mix of statuses, priorities, overdue tasks,
completed tasks and tasks without a due date) in one step:

```bash
php artisan migrate --seed
```

You can re-run just the seeder at any time with:

```bash
php artisan db:seed --class=TaskSeeder
```

### Running tests

Feature tests run against an in-memory SQLite database (configured in `phpunit.xml`), so they never
touch your local MySQL data:

```bash
php artisan test
```

### Starting the application

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` to see the dashboard.

## Routes

| Method | URI                      | Description                    |
|--------|--------------------------|---------------------------------|
| GET    | `/`                      | Dashboard (stats + task list)   |
| GET    | `/tasks`                 | Task list (same as dashboard)   |
| GET    | `/tasks/create`          | Show create task form           |
| POST   | `/tasks`                 | Store a new task                |
| GET    | `/tasks/{task}`          | View task details                |
| GET    | `/tasks/{task}/edit`     | Show edit task form              |
| PUT    | `/tasks/{task}`          | Update a task                    |
| DELETE | `/tasks/{task}`          | Delete a task                    |
| PATCH  | `/tasks/{task}/complete` | Mark a task as completed         |
| PATCH  | `/tasks/{task}/reopen`   | Reopen a completed task          |

## JSON API

A REST API is exposed under `/api` for scripting or integrating with other clients. It returns JSON
(no authentication required) and reuses the same validation rules as the web form.

| Method | URI                          | Description                                              |
|--------|------------------------------|------------------------------------------------------------|
| GET    | `/api/tasks`                 | List tasks (supports `search`, `status`, `priority`, `due`, `sort`, `direction`, `per_page`) |
| GET    | `/api/tasks/stats`           | Dashboard statistics (total, todo, in_progress, completed, overdue) |
| POST   | `/api/tasks`                 | Create a task                                              |
| GET    | `/api/tasks/{task}`          | Show a single task                                         |
| PUT    | `/api/tasks/{task}`          | Update a task                                              |
| DELETE | `/api/tasks/{task}`          | Delete a task (returns `204 No Content`)                    |
| PATCH  | `/api/tasks/{task}/complete` | Mark a task as completed                                    |
| PATCH  | `/api/tasks/{task}/reopen`   | Reopen a completed task                                     |

Example requests (adjust the base URL/port to match `php artisan serve`):

```bash
# List tasks, filtered and sorted
curl "http://127.0.0.1:8000/api/tasks?status=in_progress&priority=high&sort=due_date&direction=asc"

# Dashboard statistics
curl "http://127.0.0.1:8000/api/tasks/stats"

# Create a task
curl -X POST "http://127.0.0.1:8000/api/tasks" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title":"Ship the API","priority":"high","status":"todo","due_date":"2026-10-01"}'

# Mark a task as completed
curl -X PATCH "http://127.0.0.1:8000/api/tasks/1/complete" -H "Accept: application/json"

# Delete a task
curl -X DELETE "http://127.0.0.1:8000/api/tasks/1" -H "Accept: application/json"
```

Validation errors return `422 Unprocessable Entity` with a standard Laravel `{ "message": ..., "errors": {...} }`
body. The API is covered by feature tests in `tests/Feature/Api/TaskApiTest.php`.

## Project structure

```
app/Http/Controllers/TaskController.php
app/Http/Controllers/Api/TaskController.php
app/Http/Requests/StoreTaskRequest.php
app/Http/Requests/UpdateTaskRequest.php
app/Http/Resources/TaskResource.php
app/Models/Task.php
database/factories/TaskFactory.php
database/migrations/xxxx_create_tasks_table.php
database/seeders/TaskSeeder.php
resources/views/layouts/app.blade.php
resources/views/components/status-badge.blade.php
resources/views/components/priority-badge.blade.php
resources/views/tasks/{index,create,edit,show,_form}.blade.php
public/css/app.css
public/js/app.js
tests/Feature/TaskTest.php
tests/Feature/Api/TaskApiTest.php
```
