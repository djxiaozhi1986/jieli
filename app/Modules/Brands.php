<?php
/**
 * Created by PhpStorm.
 * User: wzq
 * Date: 2017/6/6 0006
 * Time: 11:28
 */

namespace App\Modules;

use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    public $table = 'brands';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['brand_id', 'brand_name','brand_alias','industry_id'];
    public $primaryKey = "brand_id";//主键
    /**
     * The "type" of the auto-incrementing ID.
     * 逐渐类型
     * @var string
     */
    protected $keyType = 'varchar';

    /**
     * Indicates if the IDs are auto-incrementing.
     * 自增
     * @var bool
     */
    public $incrementing = false;
    public $timestamps = false;//是否自动生成时间戳
}