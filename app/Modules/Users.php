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
    public $table = 'user';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['user_id', 'user_name', 'user_pass', 'real_name','nick_name','user_type','user_level','user_face','device_type','user_points',
        'email','phone','sex','birthday','jieli_coin','jieli_coin_frozen','new_msg','activity','questions','answers','best','join_activity',
        'pub_comment','pub_read','zan','follows','remark','intro','award','works','user_title','province_id','province_name','city_id','city_name','county_id','county_name',
        'town_id','town_name','address','industry_id','interest_range','interest_type','add_time','login_time','is_deleted','wx_id','wx_face_download','','','','','','','',''];
    public $primaryKey = "user_id";//主键
    public $timestamps = false;//是否自动生成时间戳
}