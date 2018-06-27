<?php
/**
* User: xishizhaohua@qq.com
* Date: 14-11-29
* Time: 上午10:27
*/
namespace App\libraries;
/**
 *
 */
class RSACryption
{
  protected $privateKeyFilePath;
  protected $publicKeyFilePath;
  protected $privateKey;
  protected $publicKey;
  protected $open_php_ssl_msg      = array('code' => '001', 'msg' => 'php需要openssl扩展支持');
  protected $not_found_key_file_msg      = array('code' => '002', 'msg' => '密钥或者公钥的文件路径不正确');
  protected $key_error_msg      = array('code' => '003', 'msg' => '密钥或者公钥不可用');

  /**
  * 加载rsa加密解密必要参数
  **/
  public function init()
  {
    /**
    * 密钥文件的路径
    */
    $this->privateKeyFilePath = config('C.ROOT_PATH').'/keys/rsa_private_key.pem';
    /**
    * 公钥文件的路径
    */
    $this->publicKeyFilePath = config('C.ROOT_PATH').'/keys/rsa_public_key.pem';

    if(!extension_loaded('openssl')){
      return $this->open_php_ssl_msg;
    }
    // or return '$this->open_php_ssl_msg';

    if(!file_exists($this->privateKeyFilePath) || !file_exists($this->publicKeyFilePath)){
      return $this->not_found_key_file_msg;
    }
    //or return $this->not_found_key_file_msg;
    /**
    * 生成Resource类型的密钥，如果密钥文件内容被破坏，openssl_pkey_get_private函数返回false
    */
    $this->privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyFilePath));
    /**
    * 生成Resource类型的公钥，如果公钥文件内容被破坏，openssl_pkey_get_public函数返回false
    */
    $this->publicKey = openssl_pkey_get_public(file_get_contents($this->publicKeyFilePath));
    if(!$this->privateKey || !$this->publicKey){
      return $this->key_error_msg;
    }
    return true;
  }
  /**
   * RSA利用私钥加密函数
   *
   * @param  string  $value 要加密的字符串
   * @return string  返回加密后的字符串，返回null，加密失败
   */
  public function encrypt($value)
  {
    ///////////////////////////////用公钥加密////////////////////////
    $encryptData ='';

    if (openssl_public_encrypt($value, $encryptData, $this->publicKey)) {
        /**
         * 加密后 可以base64_encode后方便在网址中传输 或者打印  否则打印为乱码
         */
        $data = str_replace(array('+','/','='),array('-','_',''),base64_encode($encryptData));
        return $data;

    } else {
        return null;
    }
  }


  /**
   * RSA利用私钥加密函数
   *
   * @param  string  $value 要解密的字符串
   * @return string  返回解密后的字符串，返回null，解密失败
   */
  public function decrypt($value)
  {
    ///////////////////////////////用私钥解密////////////////////////
    $decryptData ='';
    if (openssl_private_decrypt($this->urlsafe_b64decode($value), $decryptData, $this->privateKey)) {
        return $decryptData;

    } else {
        return null;
    }
  }
  function urlsafe_b64decode($string)
  {
     $data = str_replace(array('-','_'),array('+','/'),$string);
     $mod4 = strlen($data) % 4;
     if ($mod4) {
         $data .= substr('====', $mod4);
     }
     return base64_decode($data);
   }
}
