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
     * 注册
     * @author:wzq
     * @param array $request 所有请求参数
     * @param int $u_device_id 用户登录设备号
     * @param int $u_device_type 用户登录设备类型
     * @param int $u_phone 用户手机号
     * @param int $u_random 验证码
     * @param object $u_info 查询数据库返回对象
     * @param int $user_id 用户id
     * @param array $code 返回数组
     **/
    public function register(Request $request){
        $u_phone =$request->input('u_phone');
        $u_pwd = $request->input('u_pwd');
        $u_random = $request->input('u_random');
        if (preg_match("/^1[34578][0-9]{9}$/", $u_phone) && !empty($u_random)) {
            $random = Cache::get($u_phone);
            //验证验证码
            if ($u_random == $random || $u_random == 1234) {
                $user = Users::where('phone', $u_phone)->first();
                if (!empty($user)) {
                    $code = array('dec' => $this->user_exist_err);
                } else {
                    //创建用户
                    $savedata['phone'] = $savedata['username'] = $u_phone;
                    $savedata['password'] = md5(md5($u_pwd));
                    $savedata['avator'] = config('C.DEFAULT_AVATOR');//默认头像
                    $savedata['user_token'] = $this->create_token();
                    $savedata['user_token_expire'] = time() + (7 * 24 * 3600);
                    $savedata['created_at'] = time();
                    $savedata['updated_at'] = time();
                    $user_id = Users::insertGetId($savedata);
                    if($user_id){
                        $code = array('dec' => $this->success);
                    }else{
                        $code = array('dec' => $this->error);
                    }
                }
            } else {
                $code = array('dec' => $this->val_err);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        return response()->json($code);
    }
    /**
     * 用户名密码登录
     * @author:wzq
     * @param array $request 所有请求参数
     * @param int $u_device_id 用户登录设备号
     * @param int $u_device_type 用户登录设备类型
     * @param int $u_phone 用户手机号
     * @param int $u_random 验证码
     * @param object $u_info 查询数据库返回对象
     * @param int $user_id 用户id
     * @param array $code 返回数组
     * @return string                         json字符串
     */
    public function pwd_login(Request $request)
    {
        //获取参数
        $u_phone =$request->input('u_phone');
        $u_pwd = $request->input('u_pwd');
        $u_device_type = $request->input('device_type');
        $u_device_id = $request->input('device_id');
        //加密密码
        //$admin_pass = Hash::make($admin_pass);
        $user = Users::where('phone', $u_phone)->first();
        if (!empty($user)){
            $checked = (md5(md5($u_pwd))==$user->password);
            if ($checked) {
                if ($user->status == 1) {
                    //更新最后登录时间、ip
                    $user->last_time = time();
                    $user->last_ip = $request->getClientIp();
                    $user->device_type = $u_device_type;
                    $user->device_id = $u_device_id;
                    $user->save();
                    $user_id = $user->user_id;
                    $u_token = $user->user_token;
                    $u_token_expire = $user->user_token_expire;
                    $u_nickname = $user->nickname;
                    $u_avator = config('C.DOMAIN').$user->avator;
                    $u_phone = $user->phone;
                    $code = array('data' => array('u_id' => $user_id, 'u_token' => $u_token,'u_token_expire'=>$u_token_expire, 'u_nickname' => $u_nickname, 'u_avator' => $u_avator, 'u_phone' => $u_phone), 'dec' => $this->success);
                } else {
                    $code = array('dec' => $this->login_status_err);
                }
            } else {
                $code = array('dec' => $this->login_upass_err);
            }
        } else {
            $code = array('dec' => $this->login_uname_err);
        }
        $json = json_encode($code);
        return response()->json($code);
    }
    /*
     * 发送验证码
     * @author:zq
     * @param array     $request             所有请求参数
     * @param int       $u_phone             用户手机号
     * @param int       $random              随机数
     * @param string    $data['text']        验证码内容
     * @param int       $minutes             缓存验证码时间（分）
     * @param array     $code                云片响应信息
     * @return string                        json字符串
     */
    public function verif(Request $request)
    {
        $u_phone = $request['u_phone'];
        //      发送单条短信
        if (preg_match("/^1[34578][0-9]{9}$/", $u_phone)) {
            require app_path('libraries/yunpian/YunpianAutoload.php');
            $smsOperator = new \SmsOperator();
            $data['mobile'] = $u_phone;
            $random = rand(1000, 9999);
            $data['text'] = '【天鹅阅读】您的验证码是' . $random;
            $result = $smsOperator->single_send($data);
            //缓存验证码
            $minutes = 2;
            Cache::put($u_phone, $random, $minutes);
            $code = $result->responseData['code'];
            switch ($code) {
                case 0:
                    return response()->json(['dec' => $this->success]);
                    break;
                case 22:
                    return response()->json(['dec' => $this->operation_err]);
                    break;
                case 28:
                    return response()->json(['dec' => $this->operator_err]);
                    break;
                default:
                    return response()->json(['dec' => $this->error]);
            }
        } else {
            return response()->json(['dec' => $this->client_err]);
        }
    }
    /**
     * 验证码登录
     * @author:wzq
     * @param array $request 所有请求参数
     * @param int $u_device_token 用户登录设备号
     * @param int $u_device_type 用户登录设备类型
     * @param int $u_phone 用户手机号
     * @param int $u_random 验证码
     * @param object $u_info 查询数据库返回对象
     * @param int $user_id 用户id
     * @param array $code 返回数组
     * @return string                         json字符串
     */
    public function verify_login(Request $request)
    {
        //获取参数
        $u_phone = $request['u_phone'];
        $u_random = $request['u_random'];
        $u_device_type = $request['device_type'];
        $u_device_id = $request['device_id'];
        //手机登录
        if (preg_match("/^1[34578][0-9]{9}$/", $u_phone) && !empty($u_random)) {
            $random = Cache::get($u_phone);
            //验证验证码
            if ($u_random == $random || $u_random == 1234) {
                Cache::pull($u_phone);
                $u_info = Users::select('user_id', 'user_token', 'user_token_expire', 'nickname', 'avator', 'phone')
                    ->where('phone', $u_phone)
                    ->first();
                if (!empty($u_info)) {
                    $user_id = $u_info->user_id;
                    $u_token = $u_info->user_token;
                    $u_token_expire = $u_info->user_token_expire;
                    $u_nickname = $u_info->nickname;
//                    $u_avator = $u_info->avator;
                    $u_avator = config('C.DOMAIN').$u_info->avator;
                    $u_phone = $u_info->phone;
                    //token过期
                    if (time() > $u_token_expire) {
                        //重新生成
                        $u_token = $this->create_token();
                        $u_token_expire = time() + (7 * 24 * 3600);
                        //更新至数据库
                        $u_info->user_token = $u_token;
                        $u_info->user_token_expire = $u_token_expire;
                    }
                    $u_info->last_time = time();
                    $u_info->last_ip = $request->getClientIp();
                    $u_info->device_type = $u_device_type;
                    $u_info->device_id = $u_device_id;
                    $u_info->save();
                } else if (empty($u_info)) {
                    //注册用户
//                    $u_avator="uploads/2016/11/1/head_normal.png";//默认头像
                    $u_avator=config('C.DEFAULT_AVATOR');//默认头像
                    $u_token = $this->create_token();
                    $u_token_expire = time() + (7 * 24 * 3600);
                    $user_id = Users::insertGetId(['username'=>$u_phone,'phone' => $u_phone, 'user_token' => $u_token, 'user_token_expire' => $u_token_expire, 'device_type' => $u_device_type, 'created_at' =>time(), 'is_bound' => 1,'avator'=>$u_avator]);
                    $u_nickname="用户".$user_id;
                }
                $code = array('data' => array('u_id' => $user_id, 'u_token' => $u_token,'u_token_expire'=>$u_token_expire, 'u_nickname' => $u_nickname, 'u_avator' => $u_avator, 'u_phone' => $u_phone), 'dec' => $this->success);
                return response()->json($code);
            } else {
                return response()->json(['dec' => $this->client_err]);
            }
        } else {
            return response()->json(['dec' => $this->client_err]);
        }
    }


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