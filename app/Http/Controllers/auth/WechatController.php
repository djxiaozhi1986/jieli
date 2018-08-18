<?php
/**
 * Created by PhpStorm.
 * User: hibrother-wzq
 * Date: 2016/11/9
 * Time: 下午6:00
 */
namespace App\Http\Controllers\auth;
use App\Handler\Wechat\MessageHandler;
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

class WechatController extends Controller
{

    public function create_menu(Request $request){
        $wechat = Factory::officialAccount(config('wechat'));
        $menu = $wechat->menu;
        $buttons = [
            [
                "type" => "view",
                "name" => "天鹅阅读",
                "url"  => "http://jlapi.kakusoft.com/web/wechat/index"
            ],
            [
                "type" => "view",
                "name" => "个人中心",
                "url"  => "http://jlapi.kakusoft.com/web/wechat/userinfo"
            ],
        ];
        $menu->create($buttons);
    }
    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function server(Request $request)
    {
        $wechat = Factory::officialAccount(config('wechat'));
        $server = $wechat->server;
//        $server->push(MessageHandler::class);
        $server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    # 事件消息...
                    switch ($message->Event) {
                        case 'subscribe':
                            return "您好！欢迎关注天鹅阅读!";
                            break;
                        default:
                            return "您好！其它事件!";
                            break;
                    }
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                default:
                    return '收到其它消息';
                    break;
            }
        });
        return $server->serve();
    }

    public function oauth(Request $request){
        $wechat = Factory::officialAccount(config('wechat'));
//        $wechat = new Application(config('wechat'));
        $oauth = $wechat->oauth;
        // 未登录
        if (empty($_SESSION['wechat_user'])) {
//            session(['target_url'=>'/oauth/wechat/bound']);
            return $oauth->redirect();
            // 这里不一定是return，如果你的框架action不是返回内容的话你就得使用
            // $oauth->redirect()->send();
        }
        // 已经登录过
        $user = $_SESSION['wechat_user'];
        echo $user;
    }

    public function oauth_callback(Request $request)
    {
        $wechat = Factory::officialAccount(config('wechat'));
//        $wechat = new Application(config('wechat'));
        $oauth = $wechat->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user()->toArray();
//        var_dump($user);
        $token = $user['token']->toArray();
        $open_id = $user['original']['openid'];
//        //数据库中是否存在
        $exists = Users_vendor_login::where('open_id', '=', $open_id)->first();
        $vendor_login_id=-1;
        $user_id = null;
        if (!$exists) {
            //添加到数据库
            //创建用户
            $generate = new Generate();
            $u_token = $generate->create_token();
            $u_token_expire = time() + (7 * 24 * 3600);
            $user_id = Users::insertGetId([
                'user_token' => $u_token,
                'user_token_expire' => $u_token_expire,
                'avator' => $user['avatar'],
                'nickname' => $user['nickname'],
                'created_at' => time(),
                'updated_at' => time(),
                'is_bound' => 0
            ]);
            Users_vendor_login::insert([
                'user_id' => $user_id,
                'user_head' => $user['avatar'],
                'open_id' => $user['id'],
                'access_token' => $token['access_token'],
                'user_nickname' => $user['nickname'],
                'user_sex' => $user['original']['sex'],
                'user_logintype' => 3,
//                'unionid' => $token['unionid']
            ]);
        }else{
            $vendor_login_id=$exists->vendor_id;
            $user_id = $exists->user_id;
        }
        $wechat_user['vendor_login_id']=$vendor_login_id;
        app('session')->put('wechat_user',$wechat_user);
        $targetUrl = $request['target_url'];
//        if(strpos($targetUrl,"?")>0){
//            $targetUrl .= '&vendor_login_id='.$vendor_login_id.'&user_id='.$user_id;
//        }else{
//            $targetUrl .= '?vendor_login_id='.$vendor_login_id.'&user_id='.$user_id;
//        }
//        echo $targetUrl;die;
        echo "<script language='javascript' type='text/javascript'>";
        echo "window.location.href='$targetUrl'";
        echo "</script>";
    }
    //微信三方登录
    public function vendor_login(Request $request){
        $t = $request['t'].'/login';
        $wehcat_user = app('session')->get('wechat_user');
        $vendor_login_id = $wehcat_user['vendor_login_id'];
        //查询是否已经绑定，如果已经绑定返回客户端
        $vendor = Users_vendor_login::where('vendor_id',$vendor_login_id)->first();
        if($vendor){
            if($vendor->user_id!=0){
                //加密user_id返回给前端
                $token = Crypt::encrypt($vendor->user_id);
                $targetUrl=$t;
                if(strpos($t,"?")>0){
                    $targetUrl .= '&token='.$token.'&vendor_login_success=1';
                }else{
                    $targetUrl .= '?token='.$token.'&vendor_login_success=1';;
                }
//                var_dump($targetUrl);die;
                //跳转
                echo "<script language='javascript' type='text/javascript'>";
                echo "window.location.href='$targetUrl'";
                echo "</script>";
            }
        }
        return view('vendor_bound/wechat',['vendor_login_id'=>$vendor_login_id, 't'=>$t]);
    }
    public function test_short_url(Request $request){
        $wechat = new Application(config('wechat'));
        $url = $wechat->url;
        $res = $url->shorten($request->fullUrl());
        $url_array = json_decode($res,true);
        $short_url = $url_array['short_url'];
        echo $short_url;
    }

    /**
     * @return array
     */
    public function session_set(Request $request)
    {
//        // 写入一条数据至 session 中...
////        App('session')->put('session_test','11123343445');
//
//        $App = new Application(config('wechat'));
//        $notice = $App->notice;
//        $user_open_id = 'oKy-DuDSSFWMY_Sslu3fdOSAer0E';
//        $templateId = "zCT-gaEIWdG49JVbraJVsleDFlf6ycDy4XA21W6XIJc";
//        $url = 'http://testai.netconcepts.cn/#/app/approve/from_my';
//        $data = array(
//            "first"      => "您的请假审批已经通过",
//            "keyword1"   => "请假【事假】",
//            "keyword2"   => "家中有事",
//            "keyword3"   => "通过",
//            "keyword4"   => "无",
//            "keyword5"   => "2017-11-1 12:00",
//            "remark" => "测试模版消息",
//        );
//        $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($user_open_id)->send();
//        var_dump($result);

        $app = new Application(config('wechat'));
        $menu = $app->menu;
        $buttons = [
            [
                "type" => "view",
                "name" => "NCAI",
                "url"  => "http://ncai.netconcepts.cn/"
            ],
//            [
//                "name"       => "菜单",
//                "sub_button" => [
//                    [
//                        "type" => "view",
//                        "name" => "搜索",
//                        "url"  => "http://www.soso.com/"
//                    ],
//                    [
//                        "type" => "view",
//                        "name" => "视频",
//                        "url"  => "http://v.qq.com/"
//                    ],
//                    [
//                        "type" => "click",
//                        "name" => "赞一下我们",
//                        "key" => "V1001_GOOD"
//                    ],
//                ],
//            ],
        ];
        $menu->add($buttons);
    }
    public function session_get(Request $request)
    {
        // 获取session中键值未key的数据
        $session = app('session')->get('session_test');
        echo $session;
    }
}
