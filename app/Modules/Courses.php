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

class Courses extends Model
{
    public $table = 'courses';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['course_id', 'title', 'description', 'lecturer_id','lecturer_name','cover','old_price','now_price','video_url','opened_at','created_at',
        'create_user','updated_at','update_user','status','closed_at','is_home'];
    public $primaryKey = "course_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}