<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
	'prefix'        => '/',
    'namespace'     => 'App\Http\Controllers\Api',
    // 'middleware'    => [],
    'as'            => 'api.'
], function(){
	Route::group([
		'prefix'        => 'tiktok/',
	    'namespace'     => 'Tiktok',
	    'as'			=> 'tiktok.'
	], function(){
		Route::get('callback', 'CallbackController@index')->name('callback');
		Route::post('proinfo', 'CallbackController@proinfo')->name('proinfo');
	});
});
