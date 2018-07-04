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
 * 用户表
 */

class Users extends Model
{
    public $table = 'users';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['user_id', 'username', 'password', 'realname','nickname','phone','avator','user_token','device_type','device_id',
        'user_token_expire','is_bound','last_time','last_ip','created_at','updated_at','status'];
    public $primaryKey = "user_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}