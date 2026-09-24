@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
    <div class="page-header">
        <h1>Edit Task</h1>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('tasks.update', $task) }}" novalidate>
            @csrf
            @method('PUT')
            @include('tasks._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
