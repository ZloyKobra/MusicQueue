<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\Auth\GithubController;

// Главная страница редирект на список плейлистов
Route::get('/', fn() => redirect()->route('playlists.index'));

// GitHub OAuth
Route::get('/auth/github', [GithubController::class, 'redirect'])->name('auth.github');
Route::get('/auth/github/callback', [GithubController::class, 'callback']);

// Публичная страница плейлиста (доступна гостям)
Route::get('/p/{slug}', [PlaylistController::class, 'show'])->name('playlists.public');

// Защищённые маршруты (только для авторизованных)
Route::middleware('auth')->group(function () {
    // CRUD плейлистов
    Route::resource('playlists', PlaylistController::class)->except(['show']);
    Route::get('/playlists/{slug}', [PlaylistController::class, 'show'])
        ->name('playlists.show');

    // Очередь треков
    Route::post('/playlists/{playlist}/queue', [QueueController::class, 'addTrack'])
        ->name('queue.add');
    Route::post('/queue/{item}/vote', [QueueController::class, 'vote'])
        ->name('queue.vote');
    Route::post('/queue/{item}/skip', [QueueController::class, 'skip'])
        ->name('queue.skip');
    Route::post('/queue/{item}/play', [QueueController::class, 'play'])
        ->name('queue.play');
    Route::delete('/queue/{item}', [QueueController::class, 'remove'])
        ->name('queue.remove');
});

// Маршруты Breeze (auth, profile)
require __DIR__.'/auth.php';
