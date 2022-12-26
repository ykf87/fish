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
    return view('welcome');
});

Route::get('redirect', 'App\Http\Controllers\Api\Tiktok\CallbackController@userLogin')->name('login');
Route::get('invi', 'App\Http\Controllers\Api\IndexController@index')->name('invi');
