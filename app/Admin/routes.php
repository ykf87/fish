<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
	$router->get('tiktok-shops/addnew', 'TiktokshopController@addnew')->name('tiktok-shops.addnew');
    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('tiktok-shops', TiktokshopController::class);
});
