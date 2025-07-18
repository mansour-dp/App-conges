<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiDocController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Documentation API avec Rapidoc
Route::get('/api-docs', [ApiDocController::class, 'index'])->name('api.docs');
Route::get('/docs', [ApiDocController::class, 'index'])->name('api.docs.short'); // URL plus courte
