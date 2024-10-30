<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/redis-set/{key}/{value}', function ($key, $value) {
    Redis::set($key, $value);
    return "Key {$key} set to {$value}";
});

Route::get('/redis-get/{key}', function ($key) {
    $value = Redis::get($key);
    return $value ? "Key {$key} has value {$value}" : "Key {$key} not found";
});

Route::get('/check-redis', function () {
    try {
        return Redis::connection()->ping();
    } 
    catch (Exception $e) {
        return $e->getMessage();
    }
});