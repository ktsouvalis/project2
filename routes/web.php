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

Route::group(['prefix' => 'users_courses'], function () {
    Route::get('/', [UsersCoursesController::class, 'index'])->name('users_courses.index');
    Route::post('/unregister/{course}/{user}', [UsersCoursesController::class, 'unregister'])->name('users_courses.unregister');
    Route::post('/register/{course}/{user}', [UsersCoursesController::class, 'register'])->name('users_courses.register');
});

// Route::get('/redis-set/{key}/{value}', function ($key, $value) {
//     Redis::set($key, $value);
//     return "Key {$key} set to {$value}";
// });

// Route::get('/redis-get/{key}', function ($key) {
//     $value = Redis::get($key);
//     return $value ? "Key {$key} has value {$value}" : "Key {$key} not found";
// });

// Route::get('/check-redis', function () {
//     try {
//         return Redis::connection()->ping();
//     } 
//     catch (Exception $e) {
//         return $e->getMessage();
//     }
// });