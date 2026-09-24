@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div class="page-header">
        <h1>{{ $task->title }}</h1>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card task-detail">
        <div class="task-detail-badges">
            <x-status-badge :status="$task->status" />
            <x-priority-badge :priority="$task->priority" />
            @if ($task->isOverdue())
                <span class="badge badge-overdue">Overdue</span>
            @endif
        </div>

        <dl class="detail-list">
            <dt>Description</dt>
            <dd>{{ $task->description ?: 'No description provided.' }}</dd>

            <dt>Due Date</dt>
            <dd>{{ $task->due_date?->format('M d, Y') ?? 'No due date' }}</dd>

            <dt>Created</dt>
            <dd>{{ $task->created_at->format('M d, Y H:i') }}</dd>

            <dt>Last Updated</dt>
            <dd>{{ $task->updated_at->format('M d, Y H:i') }}</dd>

            @if ($task->completed_at)
                <dt>Completed At</dt>
                <dd>{{ $task->completed_at->format('M d, Y H:i') }}</dd>
            @endif
        </dl>

        <div class="form-actions">
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary">Edit</a>

            @if ($task->status === \App\Models\Task::STATUS_COMPLETED)
                <form method="POST" action="{{ route('tasks.reopen', $task) }}" class="inline-form" data-api-url="{{ url('/api/tasks/' . $task->id . '/reopen') }}" data-api-method="PATCH">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-icon btn-secondary" aria-label="Reopen task" title="Reopen">
                        <x-icon name="undo" />
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('tasks.complete', $task) }}" class="inline-form" data-api-url="{{ url('/api/tasks/' . $task->id . '/complete') }}" data-api-method="PATCH">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-icon btn-success" aria-label="Mark task as completed" title="Complete">
                        <x-icon name="check" />
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline-form" data-confirm="Are you sure you want to delete this task?" data-api-url="{{ url('/api/tasks/' . $task->id) }}" data-api-method="DELETE" data-api-redirect="{{ route('tasks.index') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-icon btn-danger" aria-label="Delete task" title="Delete">
                    <x-icon name="trash" />
                </button>
            </form>
        </div>
    </div>
@endsection
