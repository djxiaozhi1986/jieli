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
$app->group(['namespace' => 'Api'], function($app) {
    $app->post('/login/pwd','LoginController@pwdlogin');
    $app->post('/login/verify','LoginController@verifylogin');
    $app->post('/login/wechat','LoginController@wechatlogin');
    $app->post('/login/weibo','LoginController@weibologin');
    $app->post('/login/qq','LoginController@qqlogin');
    $app->post('/login/github','LoginController@githublogin');
});
