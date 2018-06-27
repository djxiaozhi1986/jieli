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
$router->group(['namespace' => 'app'], function($router) {
    //注册
    $router->post('/register','LoginController@register');
    //发送验证码
    $router->post('/sendverifycode','LoginController@send_verify_code');
    //绑定手机
    $router->post('/bind','LoginController@bind');
    //课程列表
    $router->get('/courses/list','CoursesController@get_courses_list');
    //课程信息
    $router->get('/courses/detail','CoursesController@get_courses_detail');
    //课程点赞数量
    $router->get('/courses/detail','CoursesController@get_courses_detail');
    //课程收藏数量
    $router->get('/courses/laudnum','CoursesController@get_laud_num');
    //课程评论列表
    $router->get('/courses/commentsnum','CoursesController@get_comments_num');
    //发表评论
    $router->post('/courses/comment','CoursesController@post_comment');
    //点赞
    $router->post('/courses/laud','CoursesController@laud');
    //消赞
    $router->post('/courses/unlaud','CoursesController@un_laud');
    //收藏
    $router->post('/courses/favorite','CoursesController@favorite');
    //取消收藏
    $router->post('/courses/unfavorite','CoursesController@un_favorite');
    //购物车查询
    $router->get('/order/cart','OrderController@get_user_cart');
    //我的订单
    $router->get('/order/my','OrderController@get_user_order');

    //用户中心-修改密码
    $router->post('/resetpwd','UserController@resetpwd');
    //用户中心-修改个人资料
    $router->post('/editprofile','UserController@editprofile');
    //用户中心-系统消息
    $router->get('/getsysmessage','UserController@getsysmessage');
});
$router->group(['namespace' => 'admin'], function($router) {
    //课程管理
    //课程点赞记录
    //课程收藏记录
    //课程评论
    //创建课程
    //课程分类管理
    //订单管理
});