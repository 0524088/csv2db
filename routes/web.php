<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\FunctionController;

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
Route::middleware(['login.check'])->group(function () {
    Route::get('/', [ViewController::class, 'index']);
    Route::get('index', [ViewController::class, 'index']);
    Route::get('upload', [ViewController::class, 'upload']);
    Route::get('chart', [ViewController::class, 'chart']);
});
Route::get('login', [ViewController::class, 'login']);


Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::get('logout', [UserController::class, 'logout']);

Route::post('upload', [FunctionController::class, 'upload']);
