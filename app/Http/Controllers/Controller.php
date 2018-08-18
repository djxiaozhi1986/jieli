<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //返回错误码信息
    protected $success = array('code' => '000000', 'msg' => '操作成功');
    protected $error = array('code' => '060002', 'msg' => '操作失败');
    protected $data_err = array('code' => '060003', 'msg' => '没有可修改（查询）的数据');
    protected $token_err = array('code' => '060004', 'msg' => 'TOKEN失效');
    protected $val_err = array('code' => '060005', 'msg' => '验证码错误');
    protected $client_err = array('code' => '060006', 'msg' => '客户端参数错误');
    protected $server_err = array('code' => '060007', 'msg' => '服务器内部错误');
    protected $relogin = array('code' => '060008', 'msg' => '需重新登录');
    protected $token_check = array('code' => '060009', 'msg' => 'TOKEN验证失败，请核对参数');
    protected $login_uname_err = array('code' => '060010', 'msg' => '用户不存在，请核对登录名');
    protected $login_upass_err = array('code' => '060011', 'msg' => '密码错误，请核对密码');
    protected $login_status_err = array('code' => '060012', 'msg' => '该用户已经被禁用，请联系管理员');
    protected $login_user_role_err = array('code' => '060013', 'msg' => '该用户未设置权限，无法登录');
    protected $operation_err = array('code' => '060014', 'msg' => '操作过于频繁');
    protected $user_exist_err = array('code' => '060015', 'msg' => '手机号已被注册');
    protected $course_disable_err = array('code' => '060016', 'msg' => '该课程无法查看');
    protected $course_close_err = array('code' => '060017', 'msg' => '该课程已经关闭');
    protected $course_nothing_err = array('code' => '060018', 'msg' => '不存在的课程');
    protected $praise_err = array('code' => '060019', 'msg' => '已经赞过，不可重复点赞');
    protected $http_file_err = array('code' => '060020', 'msg' => '没找到上传文件');
    protected $http_mime_err = array('code' => '060021', 'msg' => '上传文件格式有误');
    protected $order_repeat_err = array('code' => '060022', 'msg' => '已经购买过该课程，无需再次购买');
    protected $cart_repeat_err = array('code' => '060023', 'msg' => '购物车中已经存在该课程，请到购物车中结算');
    protected $not_sufficient_funds_err = array('code' => '060024', 'msg' => '天鹅币余额不足，请充值或更换其他支付方式');
    protected $pay_repeat_err = array('code' => '060025', 'msg' => '已完成支付，无需重复支付');
    protected $pay_repeat_err1 = array('code' => '060026', 'msg' => '订单已取消');

    public function create_token()
    {
        $prefix = 'jieli_user_token_';
        $token = $prefix . time() . rand();
        //加密token
        $token = sha1(md5($token));
        return $token;
    }

    /*
     * 检查图片Mime是否合法
     * author   xx    2016年9月21日14:31:59
     *
     */
    public function _isImg($fileType)
    {
        $type = array("jpeg", "gif", "jpg", "png");
        $fileType = strtolower($fileType);
        $fileArray = explode("/", $fileType);
        $file_type = end($fileArray);
        return in_array($file_type, $type);
    }
    public function _isAudio($fileType)
    {
        //mp3 wma rm wav midi ape flac
        $type = array("mp3", "wma", "rm", "wav","midi","ape","flac");
        $fileType = strtolower($fileType);
        $fileArray = explode("/", $fileType);
        $file_type = end($fileArray);
        return in_array($file_type, $type);
    }

    /**
     * 将Null转换成""
     */
    public function convertNull(& $val, $key ) {
        if ($val === null) {
            $val = '';
        }
    }

}
