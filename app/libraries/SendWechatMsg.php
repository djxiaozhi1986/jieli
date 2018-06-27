<?php
namespace App\libraries;
use EasyWeChat\Foundation\Application;
/**
 * 发送微信模版消息类
 */
class SendWechatMsg
{
  public $WechatMsgType=array(
    'APPROVEA_TOME'=>[
      'ID'=>1,
      'NAME'=>'待审批通知',
      'KEY'=>'X_dyvwQiDBUWVdRCwFnu4P1uoOycw84wMeW8MOtGIgU'
    ],
    'APPROVEA_RESULT'=>[
      'ID'=>2,
      'NAME'=>'审批结果通知',
      'KEY'=>'I9LmxCLK3lJRpHjJDNVanhf7qXputm8msHNsY6W26y4'
    ],
    'CRM_ALARM'=>[
      'ID'=>3,
      'NAME'=>'预约提醒',
      'KEY'=>'3AjGBBOTTkUq3Ohj8vEyvfKyfCSB6_glatShfqUFz6s'
    ],
    'CRM_TO_SALES'=>[
      'ID'=>4,
      'NAME'=>'移交客户提醒',
      'KEY'=>'x0l0u0IdFLAROdC-fN_T-xpkCXyWF7aWkRmkb17NOM8'
    ],
    'CRM_TO_PUBLIC'=>[
      'ID'=>5,
      'NAME'=>'掉入公海提醒',
      'KEY'=>'I9LmxCLK3lJRpHjJDNVanhf7qXputm8msHNsY6W26y4'
    ],
    'CRM_CLUE_TO_SALIES'=>[
      'ID'=>6,
      'NAME'=>'分配客户提醒',
      'KEY'=>'I9LmxCLK3lJRpHjJDNVanhf7qXputm8msHNsY6W26y4'
    ],
    'PAYMENT_MSG'=>[
      'ID'=>7,
      'NAME'=>'催款提醒',
      'KEY'=>'QAWVVfZHuls4atS-jH1MrzZfCVo-Ws9R16wjwytvYGk'
    ]
  );

  public function send_wechat_templete_msg($type,$open_id,$url,$msg)
  {
    $app = new Application(config('wechat'));
    $notice = $app->notice;
    $result = $notice->uses($type['KEY'])->withUrl($url)->andData($msg)->andReceiver($open_id)->send();
    return $result->code;
  }

}
