@extends('layouts.app')

@section('title', 'Add Task')

@section('content')
    <div class="page-header">
        <h1>Add Task</h1>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('tasks.store') }}" novalidate>
            @csrf
            @include('tasks._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Task</button>
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
