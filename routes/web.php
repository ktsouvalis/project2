<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersCoursesController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [UsersController::class, 'login']);

Route::get('/logout', [UsersController::class, 'logout'])->name('logout');

Route::get('/issue_token', [UsersController::class, 'issue_token'])->middleware('auth')->name('issue_token');

Route::group(['prefix' => 'users_courses', 'middleware'=>'auth'], function () {
    Route::get('/', [UsersCoursesController::class, 'index'])->name('users_courses.index');
    Route::post('/unregister/{course}/{user}', [UsersCoursesController::class, 'unregister'])->name('users_courses.unregister');
    Route::post('/register/{course}/{user}', [UsersCoursesController::class, 'register'])->name('users_courses.register');
});