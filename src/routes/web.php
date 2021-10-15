<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\LoginController;
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
    return view('welcome');
});


Route::prefix('canvas')->group(function () {
    Route::get('/login', [LoginController::class, 'index']);
    Route::any('/check_permission', [LoginController::class, 'checkPermission'])
    ->name('check_permission');
});

