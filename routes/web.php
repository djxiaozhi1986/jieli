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
$router->get('/domain/check','ExampleController@check_domain');
$router->post('/collect/resource','ExampleController@get_collect_resource');
$router->post('/cidd','ExampleController@create_industry');
$router->post('/upload_pic','ExampleController@editor_upload_pic');
$router->group(['namespace' => 'App'], function($router) {
    //公共类
    //发送短信
    $router->post('/common/msg/send','CommonController@send_message');
    //分类查询课程 *************
    $router->get('/courses/clist','CoursesController@get_courses_by_category');
    //推荐课程列表 *************
    $router->get('/courses/recomlist','CoursesController@get_home_courses_list');
    //课程列表 *************
    $router->get('/courses/list','CoursesController@get_courses_list');
    //课程信息 *************
    $router->get('/courses/detail','CoursesController@get_course_detail');
    //课程章节列表********** new
    $router->get('/courses/sections','CoursesController@get_course_sections');
    //课程评论列表 *************
    $router->get('/courses/comments','CoursesController@get_course_comments');
    $router->get('/courses/comments/children','CoursesController@get_comment_children');
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
    //同类购买-new
    $router->get('/courses/buy/similar','CoursesController@similar_course_list');
    $router->get('/courses/similar','CoursesController@classify_course_list');
    $router->get('/courses/comments/hot','CoursesController@get_hot_comments');
    $router->get('/courses/good','CoursesController@get_good_courses');
    $router->get('/courses/lecturer','CoursesController@get_lecturer_courses_list');
    $router->get('/courses/user','CoursesController@get_user_courses_list');
    $router->get('/courses/keys/hot','CoursesController@get_hot_keys');


    /*微课分类*/
    $router->get('/classify/we/first','CategorysController@getTopCategorys');
    $router->get('/classify/we/user','CategorysController@get_user_categorys');
    $router->post('/classify/we/user/add','CategorysController@add_user_category');
    $router->post('/classify/we/user/del','CategorysController@del_user_category');
    $router->post('/classify/we/user/edit','CategorysController@up_user_category');
    $router->get('/classify/we/secondary','CategorysController@getChildCategorys');


    //用户中心-修改密码 *************
    $router->post('/user/resetpwd','UserController@reset_pwd');
//    $router->post('/user/resetpwd','UserController@api_reset_pwd');

    //用户中心-修改个人资料 *************
//    $router->post('/user/editprofile','UserController@edit_profile');
    $router->post('/user/editprofile','UserController@api_set_info');
    //用户中心-修改个人头像 *************
    $router->post('/user/avator','UserController@edit_avator');
    //用户中心-修改手机号码 *************
    $router->post('/user/editphone','UserController@edit_phone');
//    $router->post('/user/editphone','UserController@api_reset_phone');
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
    //分类相关接口
    $router->post('/classify/update','UserController@api_classify_edit');
    $router->get('/classify/all','UserController@api_classify_all');
    $router->get('/classify/user','UserController@api_classify_user');
    $router->get('/classify/secondary','UserController@api_classify_secondary');
    $router->get('/classify/user/secondary','UserController@api_user_class');

    $router->get('/expert/class','UserController@api_expert_class');
    $router->get('/expert/list','UserController@api_expert_list');
    $router->post('/expert/invite','UserController@api_expert_invite');
    $router->get('/expert/detail','UserController@get_lecturer_detail');

    $router->get('/user/info','UserController@api_get_userinfo');

    $router->get('/user/msg/list','UserController@api_sys_msg_list');
    $router->post('/user/msg/del','UserController@api_sys_msg_del');
    $router->get('/user/msg/detail','UserController@api_sys_msg_detail');
    $router->get('/user/point/list','UserController@api_user_point_list');


    //购物车查询
    $router->get('/cart/my','CoursesController@my_cart');
    //添加到购物车
    $router->get('/cart/add','CoursesController@add_to_cart');
    //从购物车移除
    $router->post('/cart/del','CoursesController@del_from_cart');
    //创建订单
    $router->post('/order/create','OrderController@create');
    //天鹅币支付
    $router->get('/order/coin_pay','OrderController@coin_pay');

    //问答
    //列表
    $router->get('/answer/list','AnswerController@api_answer_list');
    //详情
    $router->get('/answer/detail','AnswerController@api_answer_detail');
    //回答
    $router->post('/answer/add','AnswerController@api_answer_add');
    //取消收藏
    $router->post('/answer/unfavorite','AnswerController@api_answer_unfavorite');
    //收藏
    $router->post('/answer/favorite','AnswerController@api_answer_favorite');
    //回复回答
    $router->post('/answer/replay','AnswerController@api_answer_reply');
    //采纳回答
    $router->post('/answer/adopt','AnswerController@api_answer_adopt');
    //点赞回答
    $router->post('/answer/zan','AnswerController@api_answer_zan');
    //回复列表
    $router->get('/answer/reply/list','AnswerController@api_answer_reply_list');
    //回复评论
    $router->post('/answer/reply/comment','AnswerController@api_answer_reply_comment');
    //回复评论列表
    $router->get('/answer/comment/list','AnswerController@api_answer_comment_list');
    //回复详情
    $router->get('/answer/reply/detail','AnswerController@api_answer_reply_detail');
    //
    $router->post('/answer/report','AnswerController@api_answer_report');
    //new
    $router->get('/answer/user/attention','AnswerController@api_answer_user_attention');
    $router->get('/answer/user','AnswerController@api_answer_user');
    $router->get('/answer/keys/hot','AnswerController@api_answer_hot_keys');
    $router->get('/answer/course/list','AnswerController@api_answer_by_course');

});
$router->group(['namespace' => 'Admin'], function($router) {
    //课程管理
    $router->post('/admin/courses/save','CoursesController@save_course');
    $router->get('/admin/courses/sections','CoursesController@get_course_sections');
    $router->post('/admin/courses/sections/add','CoursesController@add_section');
    $router->delete('/admin/courses/sections/del','CoursesController@del_section');
    $router->post('/admin/courses/publish','CoursesController@publish');
    $router->post('/admin/courses/down','CoursesController@down');
    //课程列表 *************
    $router->get('/admin/courses/list','CoursesController@get_courses_list');
    //课程明细
    $router->get('/admin/courses/detail','CoursesController@get_course_detail');
    $router->get('/admin/courses/detail1','CoursesController@get_course_detail1');
    $router->delete('/admin/courses/del','CoursesController@del_course');
    //讲师列表 *************
    $router->get('/admin/lecturer/list','CoursesController@get_lecturer_list');
    //上传文件
    $router->post('/admin/upload','CoursesController@Upload');
    $router->post('/admin/upload/audio','CoursesController@UploadAudio');
    //删除图片
    $router->delete('/admin/removefile','CoursesController@RemoveFile');
    //修改课程状态
    $router->post('/admin/courses/editstatus','CoursesController@edit_course_status');
    //修改课程音频文件，停止直播
    $router->post('/admin/courses/editaudio','CoursesController@edit_course_audio');
    $router->post('/admin/lecturer/save','CoursesController@save_lecturer');
    $router->delete('/admin/lecturer/del','CoursesController@del_lecturer');
    $router->get('/admin/category/all','CategorysController@get_choise_all_categorys');
    $router->get('/admin/category/manager','CategorysController@get_all_categorys');
    $router->post('/admin/category/save','CategorysController@saveCategory');
    $router->delete('/admin/category/del','CategorysController@delCategory');

    $router->get('/admin/comment/stay','CoursesController@get_check_course_comments');
    $router->get('/admin/comment/pass','CoursesController@get_pass_course_comments');
    $router->get('/admin/comment/refuse','CoursesController@get_refuse_course_comments');
    $router->post('/admin/comment/verify','CoursesController@comment_verify');
    $router->get('/admin/users/query','CategorysController@get_query_user');
    $router->post('/admin/answer/add','CategorysController@add_answer');

    $router->post('/admin/im/create','CoursesController@create_im_group_by_course_id');
    //课程点赞记录
    //课程收藏记录
    //课程评论
    //创建课程
    //课程分类管理
    //订单管理
});