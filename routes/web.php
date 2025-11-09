<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('uploads.index');
});

Route::get('/uploads', [App\Http\Controllers\UploadController::class, 'index'])->name('uploads.index');
Route::post('/uploads', [App\Http\Controllers\UploadController::class, 'store'])->name('uploads.store');