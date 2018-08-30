<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午5:45
 */
namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\libraries\HttpClient;
use App\Modules\Courses;
use App\Modules\Users;
use Illuminate\Http\Request;
use Cache;

class UserController extends Controller{
    /***
     * 修改密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function reset_pwd(Request $request){
        if($request->input('u_phone') && $request->input('new_pwd') && $request->input('u_random')){
            $u_phone = $request->input('u_phone');
            $u_pwd = $request->input('new_pwd');
            $u_random = $request->input('u_random');
            if (preg_match("/^1[34578][0-9]{9}$/", $u_phone)) {
                $random = Cache::get($u_phone);
                //验证验证码
                if ($u_random == $random || $u_random == 1234) {
                    $request_path = '/user/getUserMobilePass';
                    $request_url = config('C.API_URL').$request_path;
                    $params = ['u_phone'=>$u_phone,'newpass'=>$u_pwd];
                    $response = HttpClient::api_request($request_url,$params,'POST',true);
                    $code = json_decode($response);
                } else {
                    $code = array('dec' => $this->val_err);
                }
            }else{
                $code = array('dec' => $this->client_err);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
        return response()->json($code);
    }
    /***
     * 修改昵称
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit_nickname(Request $request)
    {
        if ($request->input('login_user') && $request->input('nickname')) {
            $save_data['nickname'] = $request->input('nickname');
            $res = Users::where('user_id',$request->input('login_user'))->update($save_data);
            if($res){
                $code = array('dec' => $this->success);
            }else{
                $code = array('dec' => $this->error);
            }
        } else {
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 修改个人资料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit_profile(Request $request)
    {
        if ($request->input('login_user')) {
            $save_data['sex'] = $request->input('sex');
            $save_data['birthday'] = strtotime($request->input('birthday'));
            $res = Users::where('user_id',$request->input('login_user'))->update($save_data);
            if($res){
                $code = array('dec' => $this->success);
            }else{
                $code = array('dec' => $this->error);
            }
        } else {
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 修改头像
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit_avator(Request $request){
        if($request->input('login_user')) {
            if (!$request->hasFile('u_avatar')) {
                $code = array('dec' => $this->http_file_err);
            } else {
                $file = $request->file('u_avatar');
                if ($file->isValid()) {
                    //检查mime
                    $fi = new \finfo(FILEINFO_MIME_TYPE);
                    if (!$this->_isImg($fi->file($file->getPathname()))) return response()->json(['dec' => $this->http_mime_err]);
                    $path = config('C.IMG_URL');
                    $path = $path . 'avators/';
                    $time = time();
                    $extension = $file->getClientOriginalExtension();
                    $filename = $time . '.' . $extension;
                    $file->move($path, $filename);
                    $data['avator'] = $path . $filename;
                    $res = Users::where('user_id',$request->input('login_user'))->update($data);
                    if($res){
                        $avator = config('C.DOMAIN').$path.$filename;
                        $code = array('dec' => $this->success,'data'=>$avator);
                    }else{
                        $code = array('dec' => $this->error);
                    }
                }else{
                    $code = array('dec' => $this->http_file_err);
                }
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 积分变更
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function change_score(Request $request){
        if ($request->input('login_user')) {
            $save_data['score'] = $request->input('score');
            //增量有可能为负数
            $res = DB::table('users_score_log')->where('user_id',$request->input('login_user'))->increment('score',$save_data['score']);
            if($res){
                $code = array('dec' => $this->success);
                //记录积分变化日志
                $score_data['user_id']=$request->input('login_user');
                $score_data['score']=$request->input('score');//有可能为负数
                $score_data['log_at'] = time();
                $res = DB::table('users_score_log')->create($score_data);
                if($res){
                    $code = array('dec' => $this->success);
                }else{
                    $code = array('dec' => $this->error);
                }
            }else{
                $code = array('dec' => $this->error);
            }
        } else {
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 天鹅币变更
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function change_coin(Request $request){
        if ($request->input('login_user')) {
            $save_data['e_coin'] = $request->input('coin');
            //增量有可能为负数
            $res = DB::table('users_score_log')->where('user_id',$request->input('login_user'))->increment('e_coin',$save_data['e_coin']);
            if($res){
                $code = array('dec' => $this->success);
                //记录积分变化日志
                $score_data['user_id']=$request->input('login_user');
                $score_data['coin_value']=$request->input('coin');//有可能为负数
                $score_data['log_at'] = time();
                $res = DB::table('users_coin_log')->create($score_data);
                if($res){
                    $code = array('dec' => $this->success);
                }else{
                    $code = array('dec' => $this->error);
                }
            }else{
                $code = array('dec' => $this->error);
            }
        } else {
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 获取系统消息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_sys_msg(Request $request){

    }

    /**
     * 修改手机号
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit_phone(Request $request){
        $user_id = $request->input('login_user');
        $u_phone = $request->input('phone');
        $u_new_phone = $request->input('new_phone');
        $u_random = $request->input('u_random');
        $u_new_random = $request->input('new_u_random');
        if($user_id && $u_phone && $u_new_phone && $u_random && $u_new_random){
            if (preg_match("/^1[34578][0-9]{9}$/", $u_new_phone)) {
                $c_random = Cache::get($u_phone);
                if($u_random == $c_random || $u_new_random=='1234'){
                    $c_new_random = Cache::get($u_new_phone);
                    if($u_new_random == $c_new_random || $u_new_random=='1234'){
                        $up['username'] = $u_new_phone;
                        $up['phone'] = $u_new_phone;
                        $res = $this->api_reset_phone($user_id,$u_new_phone);
                        if($res){
                            $code = array('dec'=>$this->success);
                        }else{
                            $code = array('dec'=>$this->error);
                        }
                    }else{
                        //新手机验证码错误
                        $code = array('dec'=>array('code'=>'-1','msg'=>'新手机验证码错误'));
                    }
                }else{
                    //原手机验证码错误
                    $code = array('dec'=>array('code'=>'-1','msg'=>'原手机验证码错误'));
                }
            }else{
                //新手机号码格式不正确
                $code = array('dec'=>array('code'=>'-1','msg'=>'新手机号码格式不正确'));
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    public function api_reset_phone($user_id,$u_phone){
        if($user_id && $u_phone){
            $request_path = '/user/resetMobile';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'u_phone'=>$u_phone];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $res = json_decode($response);
            if($res->dec->code=='000000'){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    public function api_classify_all(Request $request){
        $request_path = '/classify/allList';
        $request_url = config('C.API_URL').$request_path;
        $response = HttpClient::api_request($request_url,[],'POST',true);
        $code = json_decode($response);
        return response()->json($code);
    }
    public function api_classify_user(Request $request){
        $user_id = $request->input('login_user');
        if($user_id) {
            $request_path = '/classify/userList';
            $request_url = config('C.API_URL') . $request_path;
            $response = HttpClient::api_request($request_url, ['user_id'=>$user_id], 'POST', true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_classify_edit(Request $request){
        $user_id = $request->input('login_user');
        $follow_id = $request->input('follow_id');
        if($user_id && $follow_id){
            $request_path = '/classify/update';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'follow_id'=>$follow_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_classify_secondary(Request $request){
        $c_id = $request->input('c_id');
        $request_path = '/classify/getSecondary';
        $request_url = config('C.API_URL').$request_path;
        if($c_id){
            $params = ['c_id'=>$c_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
        }else{
            $response = HttpClient::api_request($request_url,["cid"=>null],'POST',true);
        }
        $code = json_decode($response);
        return response()->json($code);
    }
    public function api_expert_class(Request $request){
        $user_id = $request->input('login_user');
        if($user_id){
            $request_path = '/expert/expertClass';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_expert_list(Request $request){
        $user_id = $request->input('login_user');
        $page_index = $request->input('page_index')??1;
        $forum_id = $request->input('forum_id');
        $request_path = '/expert/list';
        $request_url = config('C.API_URL').$request_path;
        $params = ['page_index'=>$page_index];
        if($user_id){
            $params['user_id'] = $user_id;
        }
        if($forum_id){
            $params['forum_id'] = $forum_id;
        }

        $response = HttpClient::api_request($request_url,$params,'POST',true);
        $code = json_decode($response);
        return response()->json($code);
    }
    public function api_expert_invite(Request $request){
        $user_id = $request->input('login_user');
        $exper_ids = $request->input('exper_ids');
        $qa_id = $request->input('qa_id');
        if($user_id && $exper_ids && $qa_id){
            $request_path = '/expert/invite';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'exper_ids'=>$exper_ids,'qa_id'=>$qa_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_get_userinfo(Request $request){
        $user_id = $request->input('login_user');
        if($user_id){
            $user = $this->api_get_user_fun($user_id);
            if($user!=null){
                $user = json_decode(json_encode($user->detail),TRUE);
                $code = array('dec'=>$this->success,'data'=>$user);
            }else{
                //无此用户
                $code = array('dec'=>$this->login_uname_err);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_get_user_fun($user_id){
        $request_path = '/user/getUser';
        $request_url = config('C.API_URL').$request_path;
        $params = ['user_id'=>$user_id];
        $response = HttpClient::api_request($request_url,$params,'POST',true);
        $res = json_decode($response);
        $user = null;
        if($res->dec->code=='000000'){
            $user = $res->data;
        }
        return $user;
    }
    public function api_set_info(Request $request){
//        user_id 用户ID
//        email 邮箱（非必填）
//        nick_name 昵称（非必填）
//        real_name 真名（非必填）
//        birthday yyyy-MM-dd（非必填）
//        sex 性别（非必填）
        $user_id = $request->input('login_user');
        $email = $request->input('email');
        $nick_name = $request->input('nick_name');
        $real_name = $request->input('real_name');
        $birthday = $request->input('birthday');
        $sex = $request->input('sex');
        if($user_id){
            $params['user_id'] =$user_id;
            $params['email'] =$email;
            $params['nick_name'] =$nick_name;
            $params['real_name'] =$real_name;
            $params['birthday'] =$birthday;
            $params['sex'] =$sex;
            $request_path = '/user/setinfo';
            $request_url = config('C.API_URL').$request_path;
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }

    /**
     * 主讲人详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_lecturer_detail(Request $request){
        if($request->input('user_id')){
            $lecturer =Users::where('user_type',2)->where('user_id',$request->input('user_id'))->select('user_id','nick_name','user_title','real_name','user_level','user_face','phone','intro','award')->first();
//            $lecturer = Users::where('user_id',$request->input('user_id'))->first();
            if($lecturer){
                //获取主讲课程
                $lecturer['courses'] =Courses::where('lecturer_id',$request->input('user_id'))
                    ->select('course_id','title','description','coin_price','now_price','cover','is_home','is_live','opened_at','closed_at','created_at','is_oa')
                    ->skip(0)->take(3)->get()->toArray();
                //相关问答？？？？
                $code = array('dec' => $this->success, 'data' => $lecturer);
            }else{
                $code = array('dec'=>array('code'=>'060002','msg'=>'不存在的主讲人'));
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
}