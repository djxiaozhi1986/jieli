<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/8/30
 * Time: 上午10:44
 */

namespace App\Modules;


use Illuminate\Database\Eloquent\Model;
/*
 * 用户token
 */
class Users_tokens extends Model
{
    public $table = 'courses_users_tokens';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['id', 'user_id', 'user_token', 'user_token','user_token_expire'];
    public $primaryKey = "id";//主键
    public $timestamps = false;//是否自动生成时间戳

}