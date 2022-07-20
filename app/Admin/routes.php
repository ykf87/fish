<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
	$router->get('tiktok-account/addnew', 'TiktokaccountController@addnew')->name('tiktok-account.addnew');
    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('tiktok-account', TiktokaccountController::class);
    $router->resource('tiktok-shops', TiktokshopController::class);
    $router->resource('tiktok-products', TiktokProductController::class);
});
