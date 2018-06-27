<?php
namespace App\libraries;

/**
 * TOKEN生成工具
 */
class Generate
{
  protected $prefix = 'jl_user_token_';
  public function create_token()
  {
    $token = $this->prefix.time().rand();
    //加密token
    $token = sha1(md5($token));
    return $token;
  }
  protected $prefix_order = 'jl_order_no_';
  public function create_order_no()
  {
    $order_no = $this->prefix_order.time().rand();
    //加密token
    $order_no = sha1(md5($order_no));
    return $order_no;
  }
}
?>
