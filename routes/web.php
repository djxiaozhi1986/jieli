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
$router->group(['namespace' => 'App'], function($router) {
    //推荐课程列表
    $router->get('/courses/recomlist','CoursesController@get_home_courses_list');
    //课程列表
    $router->get('/courses/list','CoursesController@get_courses_list');
    //课程信息
    $router->get('/courses/detail','CoursesController@get_course_detail');
    //课程评论列表
    $router->get('/courses/comments','CoursesController@get_course_comments');
    //发表评论
    $router->post('/courses/comment','CoursesController@add_course_comment');
    //微课点赞
    $router->post('/courses/course/praise','CoursesController@add_course_praise');
    //评论点赞
    $router->post('/courses/comment/praise','CoursesController@add_comment_praise');
    //我的收藏
    $router->get('/courses/myfavorites','CoursesController@my_favorites');
    //收藏
    $router->post('/courses/favorite','CoursesController@add_favorites');
    //取消收藏
    $router->post('/courses/unfavorite','CoursesController@del_favorites');
    //购物车查询
    $router->get('/order/cart','OrderController@get_user_cart');
    //我的订单
    $router->get('/order/my','OrderController@get_user_order');

    //用户中心-修改密码
    $router->post('/user/resetpwd','UserController@reset_pwd');
    //用户中心-修改个人资料
    $router->post('/user/editprofile','UserController@edit_profile');
    //用户中心-修改个人头像
    $router->post('/user/avator','UserController@edit_avator');
    //用户中心-积分变更
    $router->post('/user/changescore','UserController@change_score');
    //用户中心-天鹅币变更
    $router->post('/user/changecoin','UserController@change_coin');
    //用户中心-系统消息
    $router->get('/user/getsysmsg','UserController@get_sys_msg');
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