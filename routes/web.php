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
$router->group(['namespace' => 'Api'], function($router) {
    $router->post('/login/pwd','LoginController@pwdlogin');
    $router->post('/login/verify','LoginController@verifylogin');
    $router->post('/login/wechat','LoginController@wechatlogin');
    $router->post('/login/weibo','LoginController@weibologin');
    $router->post('/login/qq','LoginController@qqlogin');
    $router->post('/login/github','LoginController@githublogin');
});
