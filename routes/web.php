<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\LanguageController;

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/country/{iso3}', [DashboardController::class, 'country'])->name('country.show');
    Route::get('/compare', [DashboardController::class, 'compare'])->name('compare');
});
Route::get('/ports', [PortController::class, 'index'])->name('ports.index');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
Route::get('/analytics/data/{iso3}', [AnalyticsController::class, 'data'])->name('analytics.data');

Route::middleware('auth')->group(function () {
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/ports', [AdminController::class, 'ports'])->name('ports');
    Route::post('/ports', [AdminController::class, 'storePort'])->name('ports.store');
    Route::delete('/ports/{port}', [AdminController::class, 'deletePort'])->name('ports.delete');
    Route::get('/articles', [AdminController::class, 'articles'])->name('articles');
    Route::get('/articles/create', [AdminController::class, 'createArticle'])->name('articles.create');
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('articles.store');
    Route::get('/articles/{article}/edit', [AdminController::class, 'editArticle'])->name('articles.edit');
    Route::put('/articles/{article}', [AdminController::class, 'updateArticle'])->name('articles.update');
    Route::delete('/articles/{article}', [AdminController::class, 'deleteArticle'])->name('articles.delete');
});
