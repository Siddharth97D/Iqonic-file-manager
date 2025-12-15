<?php

use Illuminate\Support\Facades\Route;
use Iqonic\FileManager\Http\Controllers\DashboardController;

Route::group([
    'prefix' => config('file-manager.route_prefix'),
    'middleware' => config('file-manager.middleware'),
], function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('file-manager.dashboard');
    Route::get('/trash', [DashboardController::class, 'trash'])->name('file-manager.trash');
    Route::get('/settings', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'index'])->name('file-manager.settings');

});
