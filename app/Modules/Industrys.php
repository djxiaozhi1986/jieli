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

class Industrys extends Model
{
    public $table = 'industrys';//数据库表名
    //数据库字段，白名单，只能查询此队列中的字段
    protected $fillable = ['industry_id', 'industry_name'];
    public $primaryKey = "industry_id";//主键
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


    //行业下的所有品牌词
    public function hasManyToBrands()
    {
        return $this->hasMany('App\Modules\Brands', 'industry_id', 'industry_id');
    }
}