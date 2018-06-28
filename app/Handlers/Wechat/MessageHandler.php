<?php
namespace App\Handler\Wechat;


use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

class MessageHandler implements EventHandlerInterface {
    public function handle($message = null){
        switch ($message['MsgType']) {
            case 'event':
                # 事件消息...
                switch ($message->Event) {
                    case 'subscribe':
                        return "您好！欢迎关注天鹅阅读!";
                        break;
                    default:
                        return "您好！其它事件!";
                        break;
                }
                break;
            case 'text':
                return '收到文字消息';
                break;
            case 'image':
                return '收到图片消息';
                break;
            case 'voice':
                return '收到语音消息';
                break;
            case 'video':
                return '收到视频消息';
                break;
            case 'location':
                return '收到坐标消息';
                break;
            case 'link':
                return '收到链接消息';
                break;
            case 'file':
                return '收到文件消息';
            default:
                return '收到其它消息';
                break;
        }
    }
}