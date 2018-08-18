<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午5:45
 */
namespace App\Http\Controllers\app;

use App\Http\Controllers\Controller;
use App\Modules\Orders;
use App\Modules\Users;
use App\Modules\Users_coin_log;
use Illuminate\Http\Request;

class OrderController extends Controller{

    /**
     * 生成订单号
     * @return string 订单号
     */
    private function generate_order_no(){
        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_no_main = date('YmdHis') . rand(10000000,99999999);
        //订单号码主体长度
        $order_no_len = strlen($order_no_main);
        $order_no_sum = 0;
        for($i=0; $i<$order_no_len; $i++){
            $order_no_sum += (int)(substr($order_no_main,$i,1));
        }
        $order_no = $order_no_main . str_pad((100 - $order_no_sum % 100) % 100,2,'0',STR_PAD_LEFT);
        return $order_no;
    }
    /***
     * 创建订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request){
        $order_type = $request->input('order_type');
        $order_amount = $request->input('order_amount');
        if($request->input('login_user') && isset($order_amount) && $request->input('order_title') && isset($order_type)){
            //创建订单
            //如果参数中携带微课id，判断为重复购买
            if($order_type==0 && $request->input('course_id')){
                $exists = Orders::where('user_id',$request->input('login_user'))->where('course_id',$request->input('course_id'))->exists();
                if($exists){
                    return response()->json(array('dec' => $this->order_repeat_err));
                }
                $order['course_id'] = $request->input('course_id');
            }
            $order['order_no'] = $this->generate_order_no();
            $order['order_title'] = $request->input('order_title');
            $order['user_id'] = $request->input('login_user');
            $order['order_type'] = $order_type;
            $order['order_amount'] = $order_amount/100;
            $order['created_at'] = time();
            $order['remarks'] = $request->input('remarks');
            $order_id = Orders::insertGetId($order);
            if($order_id){
                $code = array('dec'=>$this->success,'data'=>$order['order_no']);
//                //充值订单,支付成功做变更记录
//                if($request->input('order_type')==1){
//                    //变更用户天鹅币剩余数值
//                    $this->user_coin_change($request->input('login_user'),1,$request->input('coin_value'));
//                    //天鹅币充值订单，增加天鹅币变更记录
//                    $coin_log['user_id'] = $request->input('login_user');
//                    $coin_log['coin_value'] = $request->input('coin_value');
//                    $coin_log['change_type'] = 0;
//                    $coin_log['log_at'] = time();
//                    $coin_log['order_id'] = $order_id;
//                    Users_coin_log::create($coin_log);
//                }
            }else{
                $code = array('dec' => $this->error);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 用户天鹅币值，消费，充值
     * @param $user_id 用户id
     * @param $coin_type 天鹅币，0，消费，1，充值
     */
    private function user_coin_change($user_id,$coin_type,$coin_value){
        if($user_id){
            if($coin_type==0){
                //消费,decrement
                Users::where('user_id',$user_id)->decrement('e_coin',$coin_value);
            }elseif($coin_type==1){
                //充值,increment
                Users::where('user_id',$user_id)->increment('e_coin',$coin_value);
            }
        }
    }
    /***
     * 天鹅币支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function coin_pay(Request $request){
        if($request->input('login_user') && $request->input('order_no')){
            $order = Orders::where('user_id',$request->input('login_user'))->where('order_no',$request->input('order_no'))->first();
            if($order){
                if($order->order_status==0){
                    //判断天鹅币是否充足
                    $user = Users::where('user_id',$request->input('login_user'))->first();
                    if($user->e_coin>=$order->order_amount??0){
                        //扣款
                        $user->e_coin -= $order->order_amount;
                        $res = $user->save();
                        //订单信息补全
                        $order->order_plat = 2;
                        $order->completed_at = time();
                        $order->order_status = 1;
                        $order->save();
                        //添加天鹅币变更记录
                        //天鹅币充值订单，增加天鹅币变更记录
                        $coin_log['user_id'] = $request->input('login_user');
                        $coin_log['coin_value'] = 0-$order->order_amount;//支出
                        $coin_log['change_type'] = 3;//消费
                        $coin_log['log_at'] = time();
                        $coin_log['order_id'] = $order->order_id;
                        Users_coin_log::create($coin_log);
                        if($res){
                            $code = array('dec'=>$this->success);
                        }else{
                            $code = array('dec'=>$this->error);
                        }
                    }else{
                        $code = array('dec'=>$this->not_sufficient_funds_err);
                    }
                }elseif($order->order_status==1){
                    $code = array('dec'=>$this->pay_repeat_err);
                }else{
                    $code = array('dec'=>$this->pay_repeat_err1);
                }
            }else{
                //不存在订单
                $code = array('dec'=>$this->client_err);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 微信支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function wechat_pay(Request $request){
        if($request->input('login_user') && $request->input('order_no')){

        }
    }
    public function wechat_pay_notify(Request $request){
        if($request->input('login_user') && $request->input('order_no')){

        }
    }

    /***
     * 支付宝支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ali_pay(Request $request){
        if($request->input('login_user') && $request->input('order_no')){

        }
    }
}