<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/getWeather', [WeatherController::class, 'getWeather']);