<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2019/1/23
 * Time: 8:21 PM
 */

$order = [
    'appkey'=>'123456',
    'token'=>'56789',
    'timestamp'=>'测试商品',
    'paramsA'=>'a',
    'paramsB'=>'b'
];
ksort($order);
//2. 生成以&符链接的key=value形式的字符串
$paramString = http_build_query($order);
$strSignTmp = $paramString."&key=".'你们公司微信的key';//拼接字符串
$sign = strtoupper(MD5($strSignTmp)); // MD5 后转换成大写  签名
//$sign = "appid=123456&mch_id=56789&nonce_str=c6079b98e6aeb4a98f687800c887f6cc58df95d72cd69&body=%E6%B5%8B%E8%AF%95%E5%95%86%E5%93%81&out_trade_no=87654321&total_fee=100&spbill_create_ip=123.12.12.123¬ify_url=http%3A%2F%2Fwww.yoursite.com%2Fwxpay&trade_type=APP";
