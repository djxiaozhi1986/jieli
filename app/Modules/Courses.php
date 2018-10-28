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
    protected $fillable = ['course_id', 'title', 'description', 'lecturer_id','lecturer_name','cover','coin_price','now_price','is_live','audio_url','opened_at','created_at',
        'create_user','updated_at','update_user','status','closed_at','is_home','is_good','is_oa','praise_count','c_id','is_publish','is_try','im_group_id'];
    public $primaryKey = "course_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}