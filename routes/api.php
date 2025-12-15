<?php

use Illuminate\Support\Facades\Route;
use Iqonic\FileManager\Http\Controllers\FileController;
use Iqonic\FileManager\Http\Controllers\UploadController;
use Iqonic\FileManager\Http\Controllers\TrashController;
use Iqonic\FileManager\Http\Controllers\ShareController;
use Iqonic\FileManager\Http\Controllers\StatsController;

Route::group([
    'prefix' => config('file-manager.route_prefix') . '/api',
    'middleware' => config('file-manager.middleware'),
], function () {
    
    // Files
    Route::get('/files', [FileController::class, 'index']);
    Route::patch('/files/{file}', [FileController::class, 'update']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
    Route::post('/files/{file}/restore', [FileController::class, 'restore']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('file-manager.preview')->withTrashed();
    
    // Upload
    Route::post('/files/upload', [UploadController::class, 'upload']);

    
    // Folders (Simplified as just creating a directory or empty file marker if needed, 
    // but usually just path management. Spec asked for POST /folders)
    Route::post('/folders', [FileController::class, 'createFolder']);
    Route::get('/folders/{folder}/download', [FileController::class, 'downloadFolder']);

    // Shares
    Route::post('/files/{file}/share', [ShareController::class, 'store']);
    
    // Trash 
    Route::get('/trash', [TrashController::class, 'index'])->name('file-manager.trash.index');
    Route::delete('/trash/empty', [TrashController::class, 'empty'])->name('file-manager.trash.empty');
    Route::post('/trash/{id}/restore', [TrashController::class, 'restore'])->name('file-manager.trash.restore');
    Route::delete('/trash/{id}', [TrashController::class, 'destroy'])->name('file-manager.trash.destroy');

    // Stats
    Route::get('/stats/usage', [StatsController::class, 'index']);

    // Settings
    Route::patch('/settings', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'update']);
    Route::post('/settings/test-s3', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'testS3Connection']);

});

// Public Share Route (No Auth Middleware, but maybe throttle)
Route::get(config('file-manager.route_prefix') . '/shares/{token}', [ShareController::class, 'show']);
