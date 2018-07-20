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
    //推荐课程列表 *************
    $router->get('/courses/recomlist','CoursesController@get_home_courses_list');
    //课程列表 *************
    $router->get('/courses/list','CoursesController@get_courses_list');
    //课程信息 *************
    $router->get('/courses/detail','CoursesController@get_course_detail');
    //课程评论列表 *************
    $router->get('/courses/comments','CoursesController@get_course_comments');
    //发表评论 *************
    $router->post('/courses/comment','CoursesController@add_course_comment');
    //微课点赞 *************
    $router->post('/courses/course/praise','CoursesController@add_course_praise');
    //评论点赞 *************
    $router->post('/courses/comment/praise','CoursesController@add_comment_praise');
    //我的收藏 *************
    $router->get('/courses/myfavorites','CoursesController@my_favorites');
    //收藏 *************
    $router->post('/courses/favorite','CoursesController@add_favorites');
    //取消收藏 *************
    $router->post('/courses/unfavorite','CoursesController@del_favorites');
    //添加足迹 *************
    $router->post('/courses/addfoot','CoursesController@add_foots');
    //足迹停留，离开微课详情页使用 *************
    $router->post('/courses/footstay','CoursesController@foot_stay');


    //用户中心-修改密码 *************
    $router->post('/user/resetpwd','UserController@reset_pwd');
    //用户中心-修改个人资料 *************
    $router->post('/user/editprofile','UserController@edit_profile');
    //用户中心-修改个人头像 *************
    $router->post('/user/avator','UserController@edit_avator');
    //用户中心-修改手机号码 *************
    $router->post('/user/editphone','UserController@edit_phone');
    //用户中心-积分变更 *************
    $router->post('/user/changescore','UserController@change_score');
    //用户中心-天鹅币变更 *************
    $router->post('/user/changecoin','UserController@change_coin');
    //用户中心-系统消息
    $router->get('/user/getsysmsg','UserController@get_sys_msg');
    //用户中心-我的微课 *************
    $router->get('/user/mycourses','CoursesController@my_courses');
    //用户中心-直播微课 *************
    $router->get('/user/mylivecourses','CoursesController@my_live_courses');
    //用户中心-精品微课 *************
    $router->get('/user/mygoodcourses','CoursesController@my_good_courses');
    //用户中心-我的足迹 *************
    $router->get('/user/myfoots','CoursesController@my_foots');


    //购物车查询
    $router->get('/cart/my','CoursesController@my_cart');
    //添加到购物车
    $router->get('/cart/add','CoursesController@add_to_cart');
    //从购物车移除
    $router->delete('/cart/del','CoursesController@del_from_cart');
    //创建订单
    $router->post('/order/create','OrderController@create');
    //天鹅币支付
    $router->get('/order/coin_pay','OrderController@coin_pay');
});
$router->group(['namespace' => 'admin'], function($router) {
    //课程管理
    $router->post('/admin/courses/save','CoursesController@save_course');
    //课程列表 *************
    $router->get('/admin/courses/list','CoursesController@get_courses_list');
    //课程明细
    $router->get('/admin/courses/detail','CoursesController@get_course_detail');
    $router->delete('/admin/courses/del','CoursesController@del_course');
    //讲师列表 *************
    $router->get('/admin/lecturer/list','CoursesController@get_lecturer_list');
    //上传文件
    $router->post('/admin/upload','CoursesController@Upload');
    //删除图片
    $router->delete('/admin/removefile','CoursesController@RemoveFile');
    //修改课程状态
    $router->post('/admin/courses/editstatus','CoursesController@edit_course_status');
    //修改课程音频文件，停止直播
    $router->post('/admin/courses/editaudio','CoursesController@edit_course_audio');
    $router->post('/admin/lecturer/save','CoursesController@save_lecturer');
    $router->delete('/admin/lecturer/del','CoursesController@del_lecturer');
    //课程点赞记录
    //课程收藏记录
    //课程评论
    //创建课程
    //课程分类管理
    //订单管理
});