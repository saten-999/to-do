<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
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
