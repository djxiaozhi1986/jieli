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

class Sections extends Model
{
    public $table = 'courses_sections';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['section_id', 'course_id', 'title', 'audio_url','is_free','price','status','order_index','created_at','is_del','is_try'];
    public $primaryKey = "section_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}