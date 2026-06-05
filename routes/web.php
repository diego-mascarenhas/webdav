<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\EnsureDavApiToken;
use Illuminate\Support\Facades\Route;

Route::get('/.well-known/carddav', fn () => redirect('/dav/', 301));
Route::get('/.well-known/caldav', fn () => redirect('/dav/', 301));
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
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/events', [CalendarController::class, 'index']);
});
