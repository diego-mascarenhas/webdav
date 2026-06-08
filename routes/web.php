<?php

use App\Http\Controllers\Api\SyncWriteController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController as DavUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\EnsureDavApiToken;
use Illuminate\Support\Facades\Route;

Route::get('/.well-known/carddav', fn () => redirect()->away(
    rtrim(config('app.url'), '/').'/'.trim(config('laravelsabre.path', 'dav'), '/').'/',
    301
));
Route::get('/.well-known/caldav', fn () => redirect()->away(
    rtrim(config('app.url'), '/').'/'.trim(config('laravelsabre.path', 'dav'), '/').'/',
    301
));
Route::redirect('/dav', '/dav/', 301);

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('contacts.index');
    }

    return view('welcome', [
        'davUrl' => rtrim(config('app.url'), '/').'/'.trim(config('laravelsabre.path', 'dav'), '/').'/',
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware(EnsureDavApiToken::class)->prefix('api')->group(function () {
    Route::get('/users', [DavUserController::class, 'show']);
    Route::post('/users', [DavUserController::class, 'store']);
    Route::post('/users/link', [DavUserController::class, 'link']);
    Route::put('/users/password', [DavUserController::class, 'updatePassword']);

    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [SyncWriteController::class, 'upsertContact']);
    Route::put('/contacts/{uid}', [SyncWriteController::class, 'upsertContact']);
    Route::delete('/contacts/{uid}', [SyncWriteController::class, 'deleteContact']);

    Route::get('/events', [CalendarController::class, 'index']);
    Route::post('/events', [SyncWriteController::class, 'upsertEvent']);
    Route::put('/events/{uid}', [SyncWriteController::class, 'upsertEvent']);
    Route::delete('/events/{uid}', [SyncWriteController::class, 'deleteEvent']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [SyncWriteController::class, 'upsertTask']);
    Route::put('/tasks/{uid}', [SyncWriteController::class, 'upsertTask']);
    Route::delete('/tasks/{uid}', [SyncWriteController::class, 'deleteTask']);
});
