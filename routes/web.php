<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BoardController::class, 'index'])->name('home');

Route::resource('tasks', TaskController::class)->only(['show']);
Route::resource('users', UserController::class)->only(['show']);
