<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/6/28
 * Time: 下午2:04
 */
namespace App\Http\Controllers\OfficeAccount;
use App\Http\Controllers\Controller;
use App\libraries\Generate;
use App\Modules\Company_info;
use App\Modules\Live_list;
use App\Modules\Users;
use App\Modules\Users_vendor_login;
use Cache;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Log;

class IndexController extends Controller
{
    public function index(Request $request){
        $wechat = Factory::officialAccount(config('wechat'));
        $js = $wechat->js;
        
        $desc = '非常不错!快来围观吧~';
        $share_str = "
            title: '我正在观看',
            link: 'http://jlapi.kakusoft.com/web/wechat/index',
            imgUrl: 'http://jlapi.kakusoft.com/logo.png',
            desc: '$desc',
            success: function () {

            },
            cancel: function () {

            }";
        var_dump($share_str);
    }
}