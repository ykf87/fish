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
	    'namespace'     => 'App\Http\Controllers\Api\Tiktok',
	], function(){
		Route::get('callback', function(Request $request){
			// file_put_contents(__DIR__ . '/tk.txt', json_encode($request->all()));
			Storage::put('tk.txt', json_encode($request->all()));
		});
	});
});
