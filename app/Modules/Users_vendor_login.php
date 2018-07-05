<?php
/**
 * Created by PhpStorm.
 * User: wzq
 * Date: 2017/6/6 0006
 * Time: 11:28
 */

namespace App\Modules;

use Illuminate\Database\Eloquent\Model;

/*
 * 三方登录用户表
 */

class Users_vendor_login extends Model
{
    public $table = 'users_vendor_login';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['vendor_id', 'user_id', 'user_logintype', 'user_head', 'unionid', 'open_id', 'access_token', 'user_nickname', 'user_sex'];
    public $primaryKey = "vendor_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}
