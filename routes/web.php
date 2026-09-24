<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| These routes serve the browser-based Todo application. They return
| Blade views (HTML), not JSON. Compare this to routes/api.php, which
| serves the same data as JSON for Postman/mobile/frontend clients.
|
|   Browser -> web.php -> TaskController -> Blade view -> HTML page
|
*/

Route::get('/', [TaskController::class, 'index'])->name('dashboard');

Route::resource('tasks', TaskController::class);
Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
Route::patch('/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');
