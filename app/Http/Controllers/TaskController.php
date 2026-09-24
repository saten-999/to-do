<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display the dashboard with task statistics, filters and the task list.
     */
    public function index(Request $request): View
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
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Task::count(),
            'todo' => Task::where('status', Task::STATUS_TODO)->count(),
            'in_progress' => Task::where('status', Task::STATUS_IN_PROGRESS)->count(),
            'completed' => Task::where('status', Task::STATUS_COMPLETED)->count(),
            'overdue' => Task::whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->where('status', '!=', Task::STATUS_COMPLETED)
                ->count(),
        ];

        return view('tasks.index', [
            'tasks' => $tasks,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'priority', 'due', 'sort', 'direction']),
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        return view('tasks.create', ['task' => new Task()]);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === Task::STATUS_COMPLETED) {
            $data['completed_at'] = now();
        }

        Task::create($data);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): View
    {
        return view('tasks.show', ['task' => $task]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task): View
    {
        return view('tasks.edit', ['task' => $task]);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === Task::STATUS_COMPLETED && $task->status !== Task::STATUS_COMPLETED) {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== Task::STATUS_COMPLETED) {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Mark the specified task as completed.
     */
    public function complete(Task $task): RedirectResponse
    {
        $task->update([
            'status' => Task::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Task marked as completed.');
    }

    /**
     * Reopen a completed task.
     */
    public function reopen(Task $task): RedirectResponse
    {
        $task->update([
            'status' => Task::STATUS_TODO,
            'completed_at' => null,
        ]);

        return redirect()->back()->with('success', 'Task reopened.');
    }
}
