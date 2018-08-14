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

class Orders extends Model
{
    public $table = 'courses_orders';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['order_id','order_title', 'order_no', 'order_amount','order_plat','transaction_no',
                            'created_at','completed_at','order_status','course_id','user_id','remarks'];
    public $primaryKey = "order_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}