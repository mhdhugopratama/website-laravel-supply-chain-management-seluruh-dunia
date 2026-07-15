<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/countries', [ApiController::class, 'countries']);
Route::get('/risk', [ApiController::class, 'risk']);
Route::get('/ports', [ApiController::class, 'ports']);
Route::get('/news', [ApiController::class, 'news']);
Route::get('/currency', [ApiController::class, 'currency']);
Route::get('/rates', [ApiController::class, 'rates']);
Route::get('/weather', [ApiController::class, 'weather']);
