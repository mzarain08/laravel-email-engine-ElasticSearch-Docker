<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\OAuthController;

Route::get('/login', [\App\Http\Controllers\OAuthController::class, 'showLogin'])->name('login');
Route::get('login/outlook', [OAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
Route::get('login/outlook/callback', [OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');
//Route::get('sync-emails', [EmailController::class, 'syncEmails'])->middleware('auth');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
});

Route::get('/auth/redirect', [OAuthController::class, 'redirectToProvider']);
Route::get('/auth/outlook/callback', [OAuthController::class, 'handleProviderCallback']);

Route::middleware('auth')->group(function () {

    Route::post('/api/create-account', [EmailController::class, 'createAccount']);
    Route::get('/api/sync-data', [EmailController::class, 'syncData']);
});
