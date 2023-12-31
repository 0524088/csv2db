<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\AjaxController;

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
    Route::get('logout', [ViewController::class, 'logout']);
});

Route::get('register', [UserController::class, 'register']);
Route::get('login', [ViewController::class, 'login']);
Route::post('login', [UserController::class, 'login']);

Route::middleware(['ajax.login.check'])->group(function () {
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('upload', [AjaxController::class, 'upload']); // 上傳檔案
    Route::post('upload_finished', [AjaxController::class, 'upload_finished']); // 處理檔案

    Route::get('tables_name', [AjaxController::class, 'getTablesName']); // 取得全部 table 名
    Route::get('table_columns_info', [AjaxController::class, 'getTableColumnsInfo']); // 取得 table 下的 column 資訊
});
