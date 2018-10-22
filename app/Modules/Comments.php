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

class Comments extends Model
{
    public $table = 'courses_comments';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['comment_id','course_id', 'parent_id', 'content', 'from_user','from_user_name','to_user','to_user_name','created_at','status','praise_count','is_verify'];
    public $primaryKey = "comment_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}