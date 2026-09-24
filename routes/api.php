<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes serve the REST API for the same Todo data, returning JSON
| instead of Blade views. Use them from Postman, a JS frontend, or a
| mobile app. See API.md for full request/response documentation.
|
|   Postman/Frontend/Mobile -> api.php -> Api\TaskController -> TaskResource -> JSON
|
| All routes below are prefixed with /api and use the "api" middleware
| group (see bootstrap/app.php / RouteServiceProvider).
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/tasks/stats', [TaskController::class, 'stats'])->name('api.tasks.stats');
Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('api.tasks.complete');
Route::patch('/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('api.tasks.reopen');
Route::apiResource('tasks', TaskController::class)->except(['index'])->names('api.tasks');
Route::get('/tasks', [TaskController::class, 'index'])->name('api.tasks.index');
