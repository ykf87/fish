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
		Route::get('login', 'CallbackController@userLogin')->name('login');
		Route::post('proinfo', 'CallbackController@proinfo')->name('proinfo');
		Route::post('orderinfo', 'CallbackController@orderinfo')->name('orderinfo');
		Route::get('aggregate', 'CallbackController@aggregate')->name('aggregate');
		Route::group([
			'prefix'        => 'index/',
		    'as'			=> 'index.'
		], function(){
			Route::get('index', 'IndexController@index')->name('index');
			Route::get('ranking', 'IndexController@ranking')->name('ranking');
		});
		Route::group([
			'prefix'        => 'product/',
		    'as'			=> 'product.'
		], function(){
			Route::get('options', 'ProductController@options')->name('options');
		});
	});
});
