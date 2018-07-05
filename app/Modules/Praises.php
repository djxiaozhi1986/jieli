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

class Praises extends Model
{
    public $table = 'praises';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['praise_id', 'course_id', 'comment_id', 'from_user','created_at'];
    public $primaryKey = "praise_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}