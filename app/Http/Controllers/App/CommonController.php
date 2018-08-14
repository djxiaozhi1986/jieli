<?php
namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\libraries\HttpClient;
use Illuminate\Http\Request;
use Cache;

class CommonController extends Controller{
    public function send_message(Request $request){
        $u_phone = $request['u_phone'];
        //      发送单条短信
        if (preg_match("/^1[345678][0-9]{9}$/", $u_phone)) {
            $random = rand(100000, 999999);
            $content = rawurlencode("您的验证码是：".$random."。请不要把验证码泄露给其他人。");
            $post_data = "account=cf_jieli&password=8315d0a58a96b922c34bb833fa8068a1&mobile=".$u_phone."&content=".$content;
            $sms_url = config('C.MASSAGE_URL');
            $xml_res = $this->Post($post_data,$sms_url);
            $result = $this->xml_to_array($xml_res);
            //缓存验证码
            $minutes = 2;
            Cache::put($u_phone, $random, $minutes);
            if($result['SubmitResult']['code']==2) {
                return response()->json(['dec' => $this->success]);
            }else{
                return response()->json(['dec' => $this->error]);
            }
        } else {
            return response()->json(['dec' => $this->client_err]);
        }
    }
    function Post($curlPost,$url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }
    function xml_to_array($xml){
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches)){
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++){
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml )){
                    $arr[$key] = $this->xml_to_array( $subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }
}