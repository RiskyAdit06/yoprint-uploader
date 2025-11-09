<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/uploads', [App\Http\Controllers\UploadController::class, 'list'])->name('api.uploads.list');
Route::get('/uploads/{id}', [App\Http\Controllers\UploadController::class, 'show'])->name('api.uploads.show');
Route::post('/uploads', [App\Http\Controllers\UploadController::class, 'store'])->name('api.uploads.store');