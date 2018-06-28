<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/6/28
 * Time: 下午3:32
 */
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Users;
use Illuminate\Http\Request;
use Cache;

class LoginController extends Controller{
    /**
     * 第三方登录绑定手机号
     * @author:zq
     * @param     array               $request 所有请求参数
     * @param     int                 $u_id 用户id
     * @param     int                 $u_phone 用户手机号
     * @param     int                 $u_random 验证码
     * @param     string              $u_token 用户的token
     * @return    string              json字符串
     */
    public function vendor_bind_phone(Request $request)
    {

        $u_id = $request['u_id'];
        $u_phone = $request['u_phone'];
        $u_random = $request['u_random'];

        if (!empty($u_id) && preg_match("/^1[34578][0-9]{9}$/", $u_phone) && !empty($u_random)) {
            $random = Cache::get($u_phone);
            //验证验证码
            if ($u_random == $random || $u_random == 1234) {
                Cache::pull($u_phone);
                $phone = User_vendor_login::leftJoin('users as u', 'users_vendor_login.user_id', '=', 'u.user_id')
                    ->select('user_phone')
                    ->where('users_vendor_login.user_id',$u_id)
                    ->first();

                if (empty($phone['phone'])) {
                    $arr = Users::select('phone')
                        ->where(['is_bound' => 1, 'phone' => $u_phone])
                        ->first();
                    if (!empty($arr)) return response()->json(['dec' => $this->be_bound_err]);
                    //第三方登录绑定手机号
                    $res = Users::where('user_id', $u_id)->update(['phone' => $u_phone, 'is_bound' => 1, 'user_lastlogin_time' => time()]);
                    if (empty($res)) return response()->json(['dec' => $this->error]);
                    return response()->json(['dec' => $this->success]);
                } else {
                    return response()->json(['dec' => $this->bound_err]);
                }
            } else {
                return response()->json(['dec' => $this->client_err]);
            }
        } else {
            return response()->json(['dec' => $this->client_err]);
        }
    }
}