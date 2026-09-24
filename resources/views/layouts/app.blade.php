<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'To-Do List') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('dashboard') }}" class="brand">📝 {{ config('app.name', 'To-Do List') }}</a>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">+ Add Task</a>
        </div>
    </header>

    <main class="container page">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
