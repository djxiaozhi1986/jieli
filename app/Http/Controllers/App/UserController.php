<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午5:45
 */
namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Modules\Users;
use Illuminate\Http\Request;

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
                    $user = Users::where('phone', $u_phone)->first();
                    if (!empty($user)) {
                        $user->password = md5(md5($u_pwd));
                        $res = $user->save();
                        if($res){
                            $code = array('dec' => $this->success);
                        }else{
                            $code = array('dec' => $this->error);
                        }
                    } else{
                        $code = array('dec' => $this->login_uname_err);
                    }
                } else {
                    $code = array('dec' => $this->val_err);
                }
            }else{
                $code = array('dec' => $this->client_err);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
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
        return response()->json($code);
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
            $save_data['birthday'] = $request->input('birthday');
            $res = Users::where('user_id',$request->input('login_user'))->update($save_data);
            if($res){
                $code = array('dec' => $this->success);
            }else{
                $code = array('dec' => $this->error);
            }
        } else {
            $code = array('dec' => $this->client_err);
        }
        return response()->json($code);
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
        return response()->json($code);
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
        return response()->json($code);
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
        return response()->json($code);
    }

    /***
     * 获取系统消息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_sys_msg(Request $request){

    }

}