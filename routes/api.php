<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CompletedTaskController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Project
    Route::apiResource('projects', ProjectController::class);

    // Project Members
    Route::apiResource('projects.members', ProjectMemberController::class)
        ->only(['index', 'store', 'destroy'])
        ->scoped();

    // Project Tasks
    Route::apiResource('projects.tasks', TaskController::class)->scoped();

    // Task Completions
    Route::apiResource('tasks.completions', CompletedTaskController::class)
        ->only(['store']);

    // Task Comments
    Route::apiResource('tasks.comments', CommentController::class)
        ->only(['index', 'store', 'destroy'])
        ->scoped();
});
