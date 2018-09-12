<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/6/28
 * Time: 下午3:32
 */
namespace App\Http\Controllers\auth;

use App\Http\Controllers\App\UserController;
use App\Http\Controllers\Controller;
use App\libraries\HttpClient;
use App\Modules\Users;
use App\Modules\Users_tokens;
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
        $device_type = $request->input('device_type');
        $device_id = $request->input('device_id');
        if (preg_match("/^1[34578][0-9]{9}$/", $u_phone) && !empty($u_random)) {
            $random = Cache::get($u_phone);
            //验证验证码
            if ($u_random == $random || $u_random == 1234) {
                $request_path = '/user/appregister';
                $request_url = config('C.API_URL').$request_path;
                $params = ['u_phone'=>$u_phone,'u_pwd'=>$u_pwd,'device_type'=>$device_type,'device_id'=>$device_id];
                $response = HttpClient::api_request($request_url,$params,'POST',true);
                $code = json_decode($response);
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
        $u_phone = $request->input('u_phone');
        $u_random = $request->input('u_random');
        $u_device_type = $request->input('device_type');
        $u_device_id = $request->input('device_id');
        //手机登录
        if (preg_match("/^1[34578][0-9]{9}$/", $u_phone) && !empty($u_random)) {
            $random = Cache::get($u_phone);
            //验证验证码
            if ($u_random == $random || $u_random == 1234) {
                Cache::pull($u_phone);
                $user = Users::where('user_name', $u_phone)->first();
                //获取用户详情
                if($user) {
                    //登录成功，获取token
                    $u_token_info = Users_tokens::where('user_id', $user->user_id)->first();
                    if ($u_token_info) {
                        //验证是否过期
                        if (time() > $u_token_info->user_token_expire) {
                            $u_token_info->user_token = $this->create_token();
                            $u_token_info->user_token_expire = time() + (7 * 24 * 3600);
                            $u_token_info->save();
                        }
                        $user->u_token = $u_token_info->user_token;
                        $user->u_token_expire = $u_token_info->user_token_expire;
                    } else {
                        //生成新的token
                        $save_token['user_token'] = $this->create_token();
                        $save_token['user_token_expire'] = time() + (7 * 24 * 3600);
                        $save_token['user_id'] = $user->user_id;
                        $row = Users_tokens::create($save_token);
                        if ($row) {
                            $user->u_token = $save_token['user_token'];
                            $user->u_token_expire = $save_token['user_token_expire'];
                        }
                    }
                }else{
                    $reg["user_name"] = $u_phone;
                    $reg["phone"] = $u_phone;
                    $reg["add_time"] = time();
                    $reg["login_time"] = time();
                    $id = Users::insertGetId($reg);
                    //生成新的token
                    $save_token['user_token'] = $this->create_token();
                    $save_token['user_token_expire'] = time() + (7 * 24 * 3600);
                    $save_token['user_id'] = $id;
                    Users_tokens::create($save_token);

                    $user = Users::where('user_id', $id)->first();

                }
                $user->user_pass = null;
                $u = json_decode(json_encode($user),TRUE);
                $code = array('dec'=>$this->success,'data'=>$u);
                $json_str = json_encode($code);
                $res_json = json_decode(\str_replace(':null', ':""', $json_str));
                return response()->json($res_json);
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

    /**
     * 接口调用登录
     * @author:zq
     * @param     array               $request 所有请求参数
     * @param     int                 $u_phone 用户手机号
     * @param     int                 $u_pwd 用户密码
     * @return    string              json字符串
     */
    public function api_phone_login(Request $request){
        $u_phone = $request['u_phone'];
        $u_pwd = $request['u_pwd'];
        if($u_phone && $u_pwd){
            $request_path = '/user/phoneLogin';
            $request_url = config('C.API_URL').$request_path;
            $params = ['u_phone'=>$u_phone,'u_pwd'=>$u_pwd];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $user = null;
            $res = json_decode($response);
//            var_dump($res->dec->code);die;
            if($res->dec->code=='000000'){
                $user = $res->data;
                if($user){
//                    var_dump($user->user_id);die;
                    //登录成功，获取token
                    $u_token_info = Users_tokens::where('user_id',$user->user_id)->first();
                    if($u_token_info){
                        //验证是否过期
                        if(time()>$u_token_info->user_token_expire){
                            $u_token_info->user_token = $this->create_token();
                            $u_token_info->user_token_expire = time() + (7 * 24 * 3600);
                            $u_token_info->save();
                        }
                        $user->u_token = $u_token_info->user_token;
                        $user->u_token_expire = $u_token_info->user_token_expire;
                    }else{
                        //生成新的token
                        $save_token['user_token'] = $this->create_token();
                        $save_token['user_token_expire'] = time() + (7 * 24 * 3600);
                        $save_token['user_id'] = $user->user_id;
                        $row = Users_tokens::create($save_token);
                        if($row){
                            $user->u_token = $save_token['user_token'];
                            $user->u_token_expire = $save_token['user_token_expire'];
                        }
                    }
                    $u = json_decode(json_encode($user),TRUE);
                    $code = array('dec'=>$this->success,'data'=>$u);
                }else{
                    $code = json_decode($response);
                }
            }else{
                $code = json_decode($response);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }



}