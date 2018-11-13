<?php

namespace App\Http\Controllers;

use App\libraries\HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ExampleController extends Controller
{
    public function check_domain(Request $request)
    {
        $length = $request->input('length')??4;
//        $keys=array('when','tree','east','west','play','');

//        $i = 0;
//        while ($i<5){
            $captch_code="";
            for($i=0;$i<$length;$i++){
                //abcdefghijklmnopqrstuvwxyz
                $str="abcdefghijklmnopqrstuvwxyz";//给出一个字符串，用于生成随机验证码
                $fontcontent=substr($str,rand(0,strlen($str)),1);//每次截取一个字符
                $captch_code.=$fontcontent;//拼接
            }

            $requestParams = array(
                "Action"        =>  "CheckDomain",
                "DomainName"    =>  $captch_code.'.com',
                "FeeCommand"    =>  "create",
                "FeeCurrency"   =>  "USD",
                "Value"         =>  '1',
            );
            $val =  $this->requestAli($requestParams);
            $obj = json_decode($val);
            $result = false;
            if($obj && $obj->Avail && $obj->Avail==1){
                //可注册的域名
//                Log::info('可用域名：'.$captch_code.'.com');
//                echo '√√√√√√√√-域名《'.$captch_code.'.com》可以注册'.'\r\n';
                $result = true;
            }
//            else{
//                echo '<div style="color:red">XXXXXXXX-域名《'.$captch_code.'.com》已经被注册'.'</div>';
//            }
            $data['domain'] = $captch_code.'.com';
            $data['premium'] = $result;
            $code = array('dec'=>$this->success,'data'=>$data);

            return response()->json($code);
//            sleep(1);
//            $i++;
//        }

    }

    private $accessKeyId  = "LTAIrXgRHWlSwG6G";
    private $accessSecrec = "7rCJw6hylvgqbJE11gKzufiCq3BTOz";
    private function requestAli($requestParams)
    {
        date_default_timezone_set('UTC');
        $publicParams = array(
            "Format"        =>  "JSON",
            "Version"       =>  "2018-01-29",
            "AccessKeyId"   =>  $this->accessKeyId,
            "Timestamp"     =>  date("Y-m-d\TH:i:s\Z"),
            "SignatureMethod"   =>  "HMAC-SHA1",
            "SignatureVersion"  =>  "1.0",
            "SignatureNonce"    =>  substr(md5(rand(1,99999999)),rand(1,9),14),
        );

        $params = array_merge($publicParams, $requestParams);
        $params['Signature'] =  $this->sign($params, $this->accessSecrec);
        $uri = http_build_query($params);
        $url = 'https://domain.aliyuncs.com/?'.$uri;
//        var_dump($url);die;
        return $this->curl($url);
    }
    private function sign($params, $accessSecrec, $method="GET")
    {
        ksort($params);
        $stringToSign = strtoupper($method).'&'.$this->percentEncode('/').'&';

        $tmp = "";
        foreach($params as $key=>$val){
            $tmp .= '&'.$this->percentEncode($key).'='.$this->percentEncode($val);
        }
        $tmp = trim($tmp, '&');
        $stringToSign = $stringToSign.$this->percentEncode($tmp);

        $key  = $accessSecrec.'&';
        $hmac = hash_hmac("sha1", $stringToSign, $key, true);

        return base64_encode($hmac);
    }


    private function percentEncode($value=null)
    {
        $en = urlencode($value);
        $en = str_replace("+", "%20", $en);
        $en = str_replace("*", "%2A", $en);
        $en = str_replace("%7E", "~", $en);
        return $en;
    }

    private function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        $result=curl_exec ($ch);
        return $result;
    }

    private function outPut($msg)
    {
        echo date("Y-m-d H:i:s")."  ".$msg.PHP_EOL;
    }







    //上传图片
    public function editor_upload_pic(Request $request){
        if (!$request->hasFile('pics')) {
            $code = array('dec' => $this->client_err);
            return response()->json($code);
        }
        $file = $request->file('pics');
        if($file->isValid()){
            //检查mime
            $fi = new \finfo(FILEINFO_MIME_TYPE);
            if (!$this->_isImg($fi->file($file->getPathname()))) return 'error|您上传的不是图片';

            //上传图片
            $path = config('C.IMG_URL').'editor/';
            $time = time();
            $filename = $time.'.jpg';
            $file->move($path,$filename);
            $save_path = $path.$filename;
            $result['errno']=0;
            $result['data'][]=config('C.DOMAIN').$save_path;
            return response()->json($result);
        }
    }
    public function del_pics(Request $request){
        $imgs = $request['del_imgs'];
        foreach ($imgs as $item){
            $item=str_replace(config('C.DOMAIN'),'',$item);
            File::delete($item);
        }
        return 1;
    }
}
