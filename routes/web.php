<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

// Renders the upload page view at the root URL
Route::get('/', [UploadController::class, 'index']);

// Also supports /upload if you prefer it explicitly
Route::get('/upload', [UploadController::class, 'index']);

// Handles the file upload POST request from Dropzone
Route::post('/upload', [UploadController::class, 'store']);