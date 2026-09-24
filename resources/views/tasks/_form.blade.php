@php
    /** @var \App\Models\Task $task */
@endphp

<div class="form-group">
    <label for="title">Title <span class="required">*</span></label>
    <input
        type="text"
        id="title"
        name="title"
        value="{{ old('title', $task->title) }}"
        class="form-control @error('title') is-invalid @enderror"
        required
        autofocus
    >
    @error('title')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea
        id="description"
        name="description"
        rows="5"
        class="form-control @error('description') is-invalid @enderror"
    >{{ old('description', $task->description) }}</textarea>
    @error('description')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-row">
    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
            @foreach (\App\Models\Task::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="priority">Priority</label>
        <select id="priority" name="priority" class="form-control @error('priority') is-invalid @enderror">
            @foreach (\App\Models\Task::PRIORITIES as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('priority')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="due_date">Due Date</label>
        <input
            type="date"
            id="due_date"
            name="due_date"
            value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}"
            class="form-control @error('due_date') is-invalid @enderror"
        >
        @error('due_date')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>
