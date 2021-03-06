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
        $url = $request->url();
        $wechat = Factory::officialAccount(config('wechat'));
        $js = $wechat->jssdk;
        $share_str = "
            title: '我正在观看',
            link: '$url',
            imgUrl: 'http://jlapi.kakusoft.com/logo.png',
            desc: '非常不错!快来围观吧~',
            success: function () {

            },
            cancel: function () {

            }";
//        $js->buildConfig(array('onMenuShareQQ', 'onMenuShareWeibo'),true,false,$share_str);
        return view('wechat/index',['js'=>$js, 'share_str'=>$share_str]);
//        var_dump($share_str);
    }
    public function userinfo(Request $request){
        //获取用信息
        $user=array();
        $wechat_user = app('session')->get('wechat_user');
        if($wechat_user){
            if($wechat_user['vendor_login_id']){
                $vendor = Users_vendor_login::where('vendor_id',$wechat_user['vendor_login_id'])->first();
                if($vendor){
                    $user['vendor'] = $vendor;
                    $user['info'] = Users::where('user_id',$vendor->user_id)->first();
                }
            }
        }
        var_dump($user);
        return view('wechat/userinfo');
    }
}