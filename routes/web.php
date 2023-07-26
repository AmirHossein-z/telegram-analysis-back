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
$router->post('/api/register', 'AuthController@register');
$router->post('/api/login', 'AuthController@login');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/api/dashboard/profile', 'UserController@getUserInfo');
    $router->get('/api/refresh', 'AuthController@refresh');
    $router->get('/api/dashboard/logout', 'AuthController@logout');
    $router->get('api/channels/{userId}', 'ChannelController@getByUserId');
    $router->post('/api/dashboard/add_api_info', 'UserController@addApiInfo');
    $router->get('/api/dashboard/login_telegram', 'TelegramController@loginTelegram');
    $router->post('/api/dashboard/otp_validation', 'TelegramController@otpValidation');
    $router->get('/api/dashboard/get_all_channels', 'TelegramController@getAllUserChannelsHas');
    $router->post('/api/dashboard/set_channel', 'TelegramController@setChannelInfo');
    $router->get('/api/dashboard/channel/{channelId}', 'ChannelController@getChannel');
    // how ??? below works
    $router->get('/api/dashboard/channel/{channelId}/posts?page=?', 'ChannelController@getChannel');
});