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

$router->get('/vipkid/articles','ArticlesController@get_articles');
$router->get('/vipkid/articles/detail','ArticlesController@get_article_detail');
$router->post('/vipkid/thumb','ArticlesController@upload_thumb');
$router->post('/vipkid/articles/save','ArticlesController@save');
$router->post('/vipkid/articles/pass','ArticlesController@pass');
$router->post('/vipkid/articles/refuse','ArticlesController@refuse');
$router->delete('/vipkid/articles/del','ArticlesController@delete');
$router->get('/vipkid/new/articles','ArticlesController@api_articles');
$router->post('/vipkid/new/articles','ArticlesController@api_articles');