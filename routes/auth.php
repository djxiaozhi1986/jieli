<?php

$router->group(['namespace' => 'Auth'], function($router) {
//    $app->get('/oauth/wechat/server', 'WechatController@server');
//    $app->post('/oauth/wechat/server', 'WechatController@server');
//    $app->get('/oauth/wechat/login', 'WechatController@oauth');
//
//    $app->get('/oauth/wechat/vendor_login', ['middleware' => 'wechat.oauth','uses'=>'WechatController@vendor_login']);
//    $app->get('/oauth/wechat/oauth_callback', 'WechatController@oauth_callback');
//    $app->post('/oauth/vendor_bound', 'WechatController@vendor_bound');
//    $app->get('/oauth/session_set', 'WechatController@session_set');
//    $app->get('/oauth/session_get', 'WechatController@session_get');
//    $app->get('/oauth/create_menu', 'WechatController@create_menu');


    $router->post('/oauth/wechat/create_menu','WechatController@create_menu');
    $router->get('/oauth/wechat/server', 'WechatController@server');
    $router->post('/oauth/wechat/server', 'WechatController@server');
    $router->get('/oauth/wechat/vendor_login', ['middleware' => 'wechat.oauth','uses'=>'WechatController@vendor_login']);
    $router->get('/oauth/wechat/oauth_callback', 'WechatController@oauth_callback');

    //发送验证码
    $router->post('/sendcode','LoginController@verif');
    //注册
    $router->post('/oauth/register','LoginController@register');
    //登录
//    $router->post('/oauth/pwd','LoginController@pwd_login');
    $router->post('/oauth/verify','LoginController@verify_login');
    $router->post('/oauth/wechat',['middleware' => 'wechat.oauth','uses'=>'WechatController@wechat_login']);
//    $router->post('/oauth/weibo','LoginController@weibo_login');
//    $router->post('/oauth/qq','LoginController@qq_login');
//    $router->post('/oauth/github','LoginController@github_login');
    //绑定手机
    $router->post('/bind','LoginController@bind');

    //中转接口
    $router->post('/auth/pwd','LoginController@api_phone_login');
});