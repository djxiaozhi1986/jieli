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
    protected $val_err = array('code' => '060005', 'msg' => '验证码失效');
    protected $client_err = array('code' => '060006', 'msg' => '客户端参数错误');
    protected $server_err = array('code' => '060007', 'msg' => '服务器内部错误');
    protected $relogin = array('code' => '060008', 'msg' => '需重新登录');
    protected $token_check = array('code' => '060009', 'msg' => 'TOKEN验证失败，请核对参数');
    protected $login_uname_err = array('code' => '060010', 'msg' => '用户不存在，请核对登录名');
    protected $login_upass_err = array('code' => '060011', 'msg' => '密码错误，请核对密码');
    protected $login_status_err = array('code' => '060012', 'msg' => '该用户已经被禁用，请联系管理员');
    protected $login_user_role_err = array('code' => '060013', 'msg' => '该用户未设置权限，无法登录');
    protected $operation_err = array('code' => '060014', 'msg' => '操作过于频繁');
    protected $operator_err = array('code' => '060015', 'msg' => '运营商错误');
    protected $no_set_time = array('code' => '060016', 'msg' => '未设置预订时间区间');
    protected $exists_msg = array('code' => '060017', 'msg' => '会议室已被预定，请您重新选择会议室。');
    protected $exists_user_realname_msg = array('code' => '060018', 'msg' => '系统中已经存在此真实姓名，请加以区分');
    protected $exists_user_username_msg = array('code' => '060019', 'msg' => '系统中用户名已被占用');
    protected $login_flag_job_err = array('code' => '060020', 'msg' => '你已经离职，不允许再登录此系统');
    protected $login_crm_err = array('code' => '060021', 'msg' => '您没有权限登录NCAI-CRM系统');
}
