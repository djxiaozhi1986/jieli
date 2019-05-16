<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2019/1/22
 * Time: 1:29 PM
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\libraries\HttpClient;
use App\Modules\Carts;
use App\Modules\Comments;
use App\Modules\Courses;
use App\Modules\Favorites;
use App\Modules\Foots;
use App\Modules\Lecturers;
use App\Modules\Orders;
use App\Modules\Praises;
use App\Modules\Sections;
use App\Modules\Users;
use App\Modules\Users_courses_relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
class DashboardController extends Controller{
    public function getWeekNumber(){
        $week = $this->getWeekTime();
        $result['add_user_num'] = Users::whereBetween('add_time',$week)->count();
        $result['view_course_num'] = Foots::whereBetween('in_time',$week)->count();
        $result['view_qa_number'] = 23482;
        $result['wechat_money_sum'] = DB::table('courses_orders')->where('order_status',1)->where('order_plat',0)->sum('order_amount');
        $result['alipay_money_sum'] = DB::table('courses_orders')->where('order_status',1)->where('order_plat',1)->sum('order_amount');
        $result['coin_sum'] = DB::table('courses_orders')->where('order_status',1)->where('order_plat',2)->sum('order_amount');
        $code = array('dec'=>$this->success,'data'=>$result);
        return response()->json($code);
    }
    public function getOrder(Request $request){
        if($request->input('start') && $request->input('end') && $request->input('flag')){
            $result['timearr'] = $this->timearr($request->input('flag'),$request->input('start'),$request->input('end'));
            $result['ydata'] = $this->getOrderByTimeArray($result['timearr'],$request->input('flag'));
            $code = array('dec'=>$this->success,'data'=>$result);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }

    public function getOrderByTimeArray($timearr,$flag){
        $op_result = [];
        foreach ($timearr as $t){
            $sqlwehre = '';
            if($flag=="1"){
                $sqlwehre = "FROM_UNIXTIME(created_at, '%Y-%m-%d')='".$t."'";
            }elseif($flag=="2"){
                $sqlwehre = "FROM_UNIXTIME(created_at, '%Y-%m-%d')='".$t."'";
//                $sqlwehre = "FROM_UNIXTIME(created_at, '%Y-%m')='".date('Y-m',strtotime($t))."'";
            }elseif($flag=="4"){
                $sqlwehre = "FROM_UNIXTIME(created_at, '%Y')='".date('Y',strtotime($t))."'";
            }
            $op_result[] = DB::table('courses_orders')->where('order_status',1)->whereRaw($sqlwehre)->count();
        }
        return $op_result;
    }
    //获取时间段当中所有时间
    public function timearr($flag,$start,$end){
//        var_dump($flag);
        $monarr=array();
        if($flag=="1"){
            do{
                $monarr[] = date('Y-m-d',$start); // 取得递增天;
            }while( ($start = strtotime('+1 day', $start)) <= $end);
        }elseif($flag=="2"){
            do{
                $monarr[] = date('Y-m-d',$start); // 取得递增天;
            }while( ($start = strtotime('+1 day', $start)) <= $end);
        }elseif($flag=="3"){
            do{
                $monarr[] = date('Y-m',$start); // 取得递增月;
            }while( ($start = strtotime('+1 month', $start)) <= $end);
        }elseif($flag=="4"){
            do{
                $monarr[] = date('Y-m',$start); // 取得递增月;
            }while( ($start = strtotime('+1 month', $start)) <= $end);
        }

        return $monarr;
    }
    private function getWeekTime(){
        //当前周
        $week['start'] = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('y'));
        $week['end'] =time();
        return $week;
    }

    function zhcnToPinyin($str)//汉字转拼音函数
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }
    public function order_list(Request $request){


        $params = [
            'appkey'=>'APP190109185409100',
            'token'=>'7c7567f63f72b8e605caf820bb3ca6ae',
            'timestamp'=>time(),
        ];
        $params['paramsA']='a';
        $params['paramsB']='b';
        $params['paramsC']='c';
        $params['paramsD']='d';

        ksort($params);
        $str = '';
        foreach ($params as $key=>$item){
            $str.=$item;
        }
        $sign = md5($str);
        $url = 'http://192.168.2.2:9532/api/v1/auth/refreshSecret';
        $params['sign'] = $sign;
        $response = HttpClient::api_request($url,$params,'POST',true);
        var_dump(json_decode($response,true));die;


        var_dump(md5(md5("123")));die;
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        $type = $request->input('type')??"-1";

        $sql = Orders::where('order_status',1)->orderBy('completed_at','desc');
        if($type!="-1"){
            $sql = $sql->where('order_plat',$type);
        }
        $total = $sql->count();
        $list = $sql->select('order_title', 'order_no', 'order_amount','order_plat','transaction_no','created_at','completed_at','nick_name','real_name')
            ->leftJoin('user','user.user_id','courses_orders.user_id')
            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
        $code = array("dec"=>$this->success,'data'=>$list,'total'=>$total);
        return response()->json($code);
    }
}