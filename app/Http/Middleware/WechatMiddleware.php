<?php
/**
 * Created by PhpStorm.
 * User: hibrother-wzq
 * Date: 2016/11/11
 * Time: 下午12:40
 */
namespace App\Http\Middleware;

use Closure;
use EasyWeChat\Foundation\Application;

class WechatMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) {
            echo '请从微信客户端打开本页面!';
            return;
        }
        if (empty(app('session')->get('wechat_user'))) {
            //微信逻辑
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
                // 未登录
                $options = config('wechat');
                $options['oauth']['callback'] = $options['oauth']['callback'] . '?target_url=' . $request->getRequestUri();
                $wechat = new Application($options);
                $oauth = $wechat->oauth;
                return $oauth->redirect();
            }
        }
//        //继续登录
//        $url = $request->getRequestUri();
//        if (strpos($url,'&')===false) {
////            $user_id = session('wechat_user')['user_id'];
////            $start = strpos($url, '=')+1;
//            $targetUrl = $url;
//            echo "<script language='javascript' type='text/javascript'>";
//            echo "window.location.href='$targetUrl'";
//            echo "</script>";
//        }

//        echo "<script language='javascript' type='text/javascript'>";
//        $targetUrl = $request->getRequestUri();
//        echo "window.location.href='$targetUrl'";
//        echo "</script>";
        return $next($request);

    }
}
