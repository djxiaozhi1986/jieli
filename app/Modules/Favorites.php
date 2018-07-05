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
 * 微课表
 */

class Favorites extends Model
{
    public $table = 'favorites';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['favorite_id', 'user_id', 'course_id', 'created_at'];
    public $primaryKey = "favorite_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}