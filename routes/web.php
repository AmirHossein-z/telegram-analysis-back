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

// this routes works
// $router->get('/api/', 'UserController@index');
$router->post('/api/register', 'AuthController@register');
$router->post('/api/login', 'AuthController@login');
$router->get('/api/refresh', 'AuthController@refresh');
$router->get('/api/dashboard/logout', 'AuthController@logout');

// $router->group(['middleware' => 'auth'], function () use ($router) {
$router->get('/api/dashboard/profile', 'UserController@getUserInfo');
// });
// $router->group(['middleware' => 'auth'], function () use ($router) {
// $router->post('/register', 'AuthController@register');
// $router->post('/login', 'AuthController@login');
// });