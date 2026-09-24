<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display task statistics used by the dashboard.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total' => Task::count(),
                'todo' => Task::where('status', Task::STATUS_TODO)->count(),
                'in_progress' => Task::where('status', Task::STATUS_IN_PROGRESS)->count(),
                'completed' => Task::where('status', Task::STATUS_COMPLETED)->count(),
                'overdue' => Task::whereNotNull('due_date')
                    ->whereDate('due_date', '<', now()->toDateString())
                    ->where('status', '!=', Task::STATUS_COMPLETED)
                    ->count(),
            ],
        ]);
    }

    /**
     * Display a paginated, filterable, sortable list of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::query()
            ->search($request->string('search')->trim()->value())
            ->status($request->string('status')->value())
            ->priority($request->string('priority')->value())
            ->dueFilter($request->string('due')->value());

        $sort = $request->string('sort')->value() ?: 'created_at';
        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'due_date', 'priority', 'title', 'status'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $tasks = $query->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return TaskResource::collection($tasks)->response();
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['status'] === Task::STATUS_COMPLETED) {
            $data['completed_at'] = now();
        }

        $task = Task::create($data);

        return TaskResource::make($task)->response()->setStatusCode(201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        return TaskResource::make($task)->response();
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $data = $request->validated();

        if ($data['status'] === Task::STATUS_COMPLETED && $task->status !== Task::STATUS_COMPLETED) {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== Task::STATUS_COMPLETED) {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return TaskResource::make($task)->response();
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * Mark the specified task as completed.
     */
    public function complete(Task $task): JsonResponse
    {
        $task->update([
            'status' => Task::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return TaskResource::make($task)->response();
    }

    /**
     * Reopen a completed task.
     */
    public function reopen(Task $task): JsonResponse
    {
        $task->update([
            'status' => Task::STATUS_TODO,
            'completed_at' => null,
        ]);

        return TaskResource::make($task)->response();
    }
}
