<?php

use Illuminate\Support\Facades\Route;
use Iqonic\FileManager\Http\Controllers\DashboardController;

Route::group([
    'prefix' => config('file-manager.route_prefix'),
    'middleware' => config('file-manager.middleware'),
], function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('file-manager.dashboard');
    Route::get('/trash', [DashboardController::class, 'trash'])->name('file-manager.trash');
    // Public Shared Links
    Route::get('/shared/{token}', [\Iqonic\FileManager\Http\Controllers\ShareController::class, 'show'])->name('share.show');
    Route::post('/shared/{token}/unlock', [\Iqonic\FileManager\Http\Controllers\ShareController::class, 'unlock'])->name('share.unlock');
    Route::get('/shared/{token}/download', [\Iqonic\FileManager\Http\Controllers\ShareController::class, 'download'])->name('share.download');

    // Main Dashboard Route
    Route::get('/', [\Iqonic\FileManager\Http\Controllers\FileManagerController::class, 'index'])
        ->middleware(config('file-manager.middleware'))
        ->name('dashboard');
    Route::get('/settings', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'index'])->name('file-manager.settings');

});
