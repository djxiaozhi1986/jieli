<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/6/28
 * Time: 下午2:05
 */
//微信端页面路由列表
$router->group(['namespace' => 'OfficeAccount'], function($router) {
    $router->get('/web/wechat/index', ['middleware' => 'wechat.oauth','uses'=>'IndexController@index']);
    $router->get('/web/wechat/userinfo', ['middleware' => 'wechat.oauth','uses'=>'IndexController@userinfo']);
});