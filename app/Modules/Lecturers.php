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
 * 讲师表
 */

class Lecturers extends Model
{
    public $table = 'lecturers';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['lecturer_id', 'lecturer_name','lecturer_title', 'description','lecturer_avator','created_at','create_user','updated_at','update_user','status'];
    public $primaryKey = "lecturer_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}