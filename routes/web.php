<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->get('/me', 'Auth\\LoginController@userDetails');
        $router->get('/logout', 'Auth\\LoginController@logout');
        $router->get('/check-login', 'Auth\\LoginController@checkLogin');
    });
});
