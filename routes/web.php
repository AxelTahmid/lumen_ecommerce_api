<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/login', 'Auth\\LoginController@login');
    $router->post('/register', 'Auth\\RegisterController@register');

    $router->group(['prefix' => 'category'], function () use ($router) {
        $router->get('/', 'CategoriesController@index');
        $router->get('/htmltree', 'CategoriesController@getCategoryHtmlTree');
        $router->get('/menutree', 'CategoriesController@getCategoryMenuHtmlTree');
        $router->get('/featured-categories', 'CategoriesController@featuredCategories');
        $router->get('/{id}', 'CategoriesController@show');
    });

    $router->group(['prefix' => 'brand'], function () use ($router) {
        $router->get('/', 'BrandsController@index');
        $router->get('/brands-by-category', 'BrandsController@brandsByCategory');
        $router->get('/{id}', 'BrandsController@show');
    });

    $router->group(['prefix' => 'product'], function () use ($router) {
        $router->get('/', 'ProductsController@index');
        $router->get('/slider-products', 'ProductsController@sliderProducts');
        $router->get('/latest-products', 'ProductsController@latestProducts');
        $router->get('/featured-products', 'ProductsController@featuredProducts');
        $router->get('/search-products', 'ProductsController@searchProducts');
        $router->get('/{id}', 'ProductsController@show');
    });

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('/', 'UsersController@index');
        $router->get('/{id}', 'UsersController@show');
    });

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->get('/me', 'Auth\\LoginController@userDetails');
        $router->get('/logout', 'Auth\\LoginController@logout');
        $router->get('/check-login', 'Auth\\LoginController@checkLogin');
        $router->post('/update-profile', 'Auth\\LoginController@updateProfile');

        $router->group(['prefix' => 'category'], function () use ($router) {
            $router->post('/', 'CategoriesController@store');
            $router->put('/{id}', 'CategoriesController@update');
            $router->delete('/{id}', 'CategoriesController@destroy');
        });

        $router->group(['prefix' => 'brand'], function () use ($router) {
            $router->post('/', 'BrandsController@store');
            $router->put('/{id}', 'BrandsController@update');
            $router->delete('/{id}', 'BrandsController@destroy');
        });

        $router->group(['prefix' => 'product'], function () use ($router) {
            $router->post('/', 'ProductsController@store');
            $router->put('/{id}', 'ProductsController@update');
            $router->delete('/delete-image/{id}', 'ProductsController@destroyImage');
            $router->delete('/{id}', 'ProductsController@destroy');
        });

        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->post('/', 'UsersController@store');
            $router->put('/{id}', 'UsersController@update');
            $router->delete('/{id}', 'UsersController@destroy');
        });

        $router->group(['prefix' => 'cart'], function () use ($router) {
            $router->get('/', 'ShoppingCartController@index');
            $router->post('/', 'ShoppingCartController@store');
            $router->put('/', 'ShoppingCartController@update');
            $router->get('/{id}', 'ShoppingCartController@show');
            $router->delete('/clearAll', 'ShoppingCartController@clearAll');
            $router->delete('/{id}', 'ShoppingCartController@destroy');
        });

        $router->group(['prefix' => 'shippingAddress'], function () use ($router) {
            $router->get('/', 'ShippingAddressesController@index');
            $router->post('/', 'ShippingAddressesController@store');
            $router->get('/{id}', 'ShippingAddressesController@show');
            $router->put('/{id}', 'ShippingAddressesController@update');
            $router->delete('/{id}', 'ShippingAddressesController@destroy');
        });

        $router->group(['prefix' => 'paymentMethods'], function () use ($router) {
            $router->get('/', 'PaymentMethodsController@index');
        });

        $router->group(['prefix' => 'orders'], function () use ($router) {
            $router->get('/', 'OrdersController@index');
            $router->post('/', 'OrdersController@store');
            $router->get('/latest-pending-orders', 'OrdersController@getLatestPendingOrders');
            $router->get('/{id}', 'OrdersController@show');
            $router->put('/{id}', 'OrdersController@update');
        });
    });
});
