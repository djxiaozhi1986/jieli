<?php

namespace App\Http\Controllers;

use App\libraries\HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function create_industry(Request $request){
        $json_str = '[
  {
		"name": "物流",
		"brands": [
		  {
				"name": "顺丰"
			},
			{
				"name": "圆通"
			},
			{
				"name": "申通"
			},
			{
				"name": "EMS"
			},
			{
				"name": "德邦物流"
			},
			{
				"name": "韵达"
			}
		]
	},
	{
		"name": "互联网",
		"brands": [{
				"name": "新浪"
			},
			{
				"name": "搜狐"
			},
			{
				"name": "360"
			},
			{
				"name": "腾讯"
			}
		]
	},
	{
		"name": "游戏",
		"brands": [{
				"name": "全民行动"
			},
			{
				"name": "荒野求生"
			},
			{
				"name": "街头篮球"
			},
			{
				"name": "NBA2018"
			},
			{
				"name": "FIFA2019"
			},
			{
				"name": "穿越火线"
			}
		]
	},
	{
		"name": "手游",
		"brands": [{
			"name": "王者荣耀"
		}]
	},
	{
		"name": "电脑配件",
		"brands": [{
				"name": "NVIDIA TESLA V100"
			},
			{
				"name": "AMD Radeon Pro SSG 16G"
			},
			{
				"name": "三星(SAMSUNG) 860 EVO 500G"
			},
			{
				"name": "戴尔U2718Q"
			}
		]
	},
	{
		"name": "计算机软件",
		"brands": [{
				"name": "Parallels Desktop"
			},
			{
				"name": "jetbrains"
			},
			{
				"name": "用友"
			},
			{
				"name": "photoshop"
			},
			{
				"name": "迅雷"
			}
		]
	},
	{
		"name": "快销",
		"brands": [{
				"name": "乐吧"
			},
			{
				"name": "可口可乐"
			},
			{
				"name": "双汇火腿"
			}
		]
	},
	{
		"name": "金融",
		"brands": [{
				"name": "中国银行"
			},
			{
				"name": "招商银行"
			},
			{
				"name": "嘉富诚"
			}
		]
	},
	{
		"name": "保险",
		"brands": [{
				"name": "太平洋保险"
			},
			{
				"name": "中国人寿"
			},
			{
				"name": "泰康人寿"
			},
			{
				"name": "平安保险"
			}
		]
	},
	{
		"name": "母婴",
		"brands": [{
				"name": "babyshop"
			},
			{
				"name": "宝宝树"
			},
			{
				"name": "妈咪团"
			},
			{
				"name": "丽家宝贝"
			},
			{
				"name": "乐友"
			},
			{
				"name": "合生元"
			},
			{
				"name": "惠氏启赋"
			}
		]
	},
	{
		"name": "教育",
		"brands": [{
				"name": "学而思"
			},
			{
				"name": "优胜教育"
			}
		]
	},
	{
		"name": "培训",
		"brands": [{
				"name": "北大青鸟"
			},
			{
				"name": "达内"
			},
			{
				"name": "尚德机构"
			},
			{
				"name": "新东方"
			}
		]
	},
	{
		"name": "传统家居",
		"brands": [{
				"name": "博洛尼"
			},
			{
				"name": "宜家家居"
			},
			{
				"name": "曲美家居"
			},
			{
				"name": "索菲亚"
			}
		]
	},
	{
		"name": "餐饮",
		"brands": [{
				"name": "全聚德"
			},
			{
				"name": "吉野家"
			},
			{
				"name": "海底捞"
			},
			{
				"name": "星巴克"
			}
		]
	},
	{
		"name": "新能源",
		"brands": [{
				"name": "大唐集团"
			},
			{
				"name": "北汽新能源"
			},
			{
				"name": "银河电子"
			}
		]
	},
	{
		"name": "旅游",
		"brands": [{
				"name": "途牛"
			},
			{
				"name": "携程"
			},
			{
				"name": "马蜂窝"
			},
			{
				"name": "驴妈妈"
			}
		]
	},
	{
		"name": "体育品牌",
		"brands": [{
				"name": "耐克"
			},
			{
				"name": "安德玛"
			},
			{
				"name": "李宁"
			},
			{
				"name": "阿迪达斯"
			}
		]
	},
	{
		"name": "娱乐",
		"brands": [{
				"name": "黄晓明"
			},
			{
				"name": "杨超越"
			},
			{
				"name": "李诞"
			}
		]
	},
	{
		"name": "汽车",
		"brands": [{
				"name": "奔驰"
			},
			{
				"name": "宝马"
			},
			{
				"name": "大众"
			}
		]
	},
	{
		"name": "房地产",
		"brands": [{
				"name": "碧桂园"
			},
			{
				"name": "恒大地产"
			}
		]
	},
	{
		"name": "短租",
		"brands": [{
				"name": "熊猫公寓"
			},
			{
				"name": "小猪短租"
			}
		]
	},
	{
		"name": "民宿",
		"brands": [{
				"name": "airbnb"
			},
			{
				"name": "携程"
			},
			{
				"name": "途牛"
			}
		]
	},
	{
		"name": "房产服务",
		"brands": [{
				"name": "链家网"
			},
			{
				"name": "贝壳找房"
			},
			{
				"name": "Q房网"
			},
			{
				"name": "安居客"
			},
			{
				"name": "我爱我家"
			},
			{
				"name": "自如"
			}
		]
	},
	{
		"name": "生活服务",
		"brands": [{
				"name": "58同城"
			},
			{
				"name": "赶集网"
			}
		]
	},
	{
		"name": "网约车",
		"brands": [{
				"name": "滴滴出行"
			},
			{
				"name": "神州专车"
			},
			{
				"name": "首汽约车"
			}
		]
	},
	{
		"name": "共享出行",
		"brands": [{
				"name": "滴滴出行"
			},
			{
				"name": "ofo"
			},
			{
				"name": "摩拜单车"
			}
		]
	},
	{
		"name": "互联网金融",
		"brands": [{
				"name": "京东金融"
			},
			{
				"name": "蚂蚁金服"
			},
			{
				"name": "360金融"
			}
		]
	},
	{
		"name": "手机",
		"brands": [{
				"name": "华为HUAWEI P20"
			},
			{
				"name": "小米Mix3"
			},
			{
				"name": "Apple iPhone X"
			}
		]
	},
	{
		"name": "电脑",
		"brands": [{
			"name": "Apple MacBook Pro"
		}]
	},
	{
		"name": "人工智能",
		"brands": [{
				"name": "谷歌AI"
			},
			{
				"name": "百度AI"
			}
		]
	},
	{
		"name": "数字钱包",
		"brands": [{
				"name": "AToken"
			},
			{
				"name": "imToken"
			}
		]
	},
	{
		"name": "票务",
		"brands": [{
				"name": "大麦"
			},
			{
				"name": "猫眼"
			},
			{
				"name": "摩天轮"
			}
		]
	},
	{
		"name": "智能家居",
		"brands": [{
				"name": "米家"
			},
			{
				"name": "海尔U-Home"
			},
			{
				"name": "M-Smart"
			},
			{
				"name": "霍尼韦尔"
			}
		]
	},
	{
		"name": "节能环保",
		"brands": [{
				"name": "西门子"
			},
			{
				"name": "飞利浦"
			}
		]
	},
	{
		"name": "生物医药",
		"brands": [{
				"name": "同仁堂"
			},
			{
				"name": "云南白药"
			},
			{
				"name": "武汉生物"
			}
		]
	},
	{
		"name": "健康管理",
		"brands": [{
				"name": "春雨医生"
			},
			{
				"name": "汤臣倍健"
			},
			{
				"name": "纽崔莱"
			}
		]
	},
	{
		"name": "物联网",
		"brands": [{
			"name": "阿里云"
		}]
	},
	{
		"name": "宠物粮食",
		"brands": [{
				"name": "皇家"
			},
			{
				"name": "渴望"
			}
		]
	},
	{
		"name": "海外购",
		"brands": [{
				"name": "网易考拉"
			},
			{
				"name": "洋码头"
			}
		]
	},
	{
		"name": "服装",
		"brands": [{
				"name": "VEROMODA"
			},
			{
				"name": "ONLY"
			},
			{
				"name": "优衣库"
			},
			{
				"name": "SELECTED"
			},
			{
				"name": "JACK JONES"
			}
		]
	}
]';
//        $json_str = json_encode($json_str);
       $arr =  json_decode($json_str,true);
//       var_dump(count($arr));
//die;
        foreach ($arr as $value){
//            var_dump($value["name"]);
            $data['industry_id'] = "".$this->create_token();
            $data['industry_name'] = $value["name"];
            DB::table('industrys')->insert($data);
            $industry_id = $data['industry_id'];
            foreach ($value['brands'] as $br){
                $b_data['brand_id'] =  "".$this->create_token();
                $b_data['brand_name'] = $br['name'];
                $b_data['industry_id'] = $industry_id;
                DB::table('brands')->insert($b_data);
            }
        }

    }

    /**
     * 获取所有bi挖掘所用的资源
     */
    public function get_collect_resource(Request $request){
        $industrys = DB::table('industrys')->get();
        $brands = DB::table('brands')->get();
        $industrys =  json_decode( json_encode( $industrys),true);
        $brands =  json_decode( json_encode( $brands),true);
//        var_dump($brands);die;
        foreach ($industrys as $key=>$value){
            foreach ($brands as $value1){
                if($value['industry_id'] == $value1['industry_id']){
                    $industrys[$key]['brands'][] = $value1;
                }
            }
        }
        $code = array('dec'=>$this->success,'data'=>$industrys);
        return response()->json($code);
    }
}
