<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersCoursesController;

Route::post('/register/{course}', [UsersCoursesController::class, 'api_register'])->name('users_courses.api.register')->middleware('auth:api');
Route::post('/unregister/{course}', [UsersCoursesController::class, 'api_unregister'])->name('users_courses.api.unregister')->middleware('auth:api');