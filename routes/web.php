<?php
use App\Http\Controllers\ScanController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

// Upload page (root and explicit /upload both render it)
Route::get('/', [UploadController::class, 'index']);
Route::get('/upload', [UploadController::class, 'index'])->name('upload.create');

// Receives the files from Dropzone
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

// Dashboard and its data endpoints
Route::get('/dashboard', [ScanController::class, 'dashboard'])->name('scans.dashboard');
Route::get('/scans', [ScanController::class, 'index'])->name('scans.index');
Route::get('/scans/status', [ScanController::class, 'status'])->name('scans.status');
Route::get('/json', [ScanController::class, 'json'])->name('scans.json');