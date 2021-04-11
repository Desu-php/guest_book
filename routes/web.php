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

Route::get('/', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');

Route::post('/upload', [\App\Http\Controllers\MessageController::class, 'uploadImage'])->name('messages.upload')
    ->middleware('auth');

Route::post('/', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store')
    ->middleware('auth');

Route::put('/', [\App\Http\Controllers\MessageController::class, 'update'])->name('messages.update')
    ->middleware('auth');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
