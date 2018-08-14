<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/8/11
 * Time: 下午2:03
 */

namespace App\libraries;

/**
 * HTTP请求类
 * @author
 * @version 2.0 2012-04-20
 */
class HttpClient
{
    public static function api_request($url,$data=null,$method='GET',$json){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if($data && is_array($data)){
            $data_string =  json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }
    /**
     * 发起一个HTTP/HTTPS的请求
     * @param $url 接口的URL
     * @param $params 接口参数   array('content'=>'test', 'format'=>'json');
     * @param $method 请求类型    GET|POST
     * @param $multi 图片信息
     * @param $extheaders 扩展的包头信息
     * @return string
     */
    public static function request( $url , $params = array(), $method = 'GET',$json = true, $multi = false, $extheaders = array())
    {
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
//        curl_setopt($ci, CURLOPT_USERAGENT, 'PHP-SDK OAuth2.0');

//        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, -1);
//        curl_setopt($ci, CURLOPT_TIMEOUT, -1);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ci, CURLOPT_SSLVERSION, 1);
//        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);

        if($json){
            if($params && is_array($params)){
                $data = json_encode( $params );
                curl_setopt($ci, CURLOPT_HEADER, 0);
                curl_setopt($ci, CURLOPT_HTTPHEADER,array('Content-Type: application/json; charset=utf-8','Content-Length:' . strlen($data)));
//                var_dump(array('Content-Type: application/json; charset=utf-8','Content-Length:' . strlen($data)));die;
            }
        }
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }
        $response = curl_exec($ci);
        var_dump(curl_error($ci));
        curl_close ($ci);
        var_dump($response);die;
        return $response;
    }
    public function ihttp_request($url, $post = '', $extra = array(), $timeout = 60) {
        $urlset = parse_url($url);
        if (empty($urlset['path'])) {
            $urlset['path'] = '/';
        }
        if (!empty($urlset['query'])) {
            $urlset['query'] = "?{$urlset['query']}";
        }
        if (empty($urlset['port'])) {
            $urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
        }
        if (!strpos($url, 'https://') && !extension_loaded('openssl')) {
            if (!extension_loaded("openssl")) {
                message('请开启您PHP环境的openssl');
            }
        }
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
//            if (ver_compare(phpversion(), '5.6') >= 0) {
//                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
//            }
            if (!empty($extra['ip'])) {
                $extra['Host'] = $urlset['host'];
                $urlset['host'] = $extra['ip'];
                unset($extra['ip']);
            }
            curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            @curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            if ($post) {
                if (is_array($post)) {
                    $filepost = false;
                    foreach ($post as $name => $value) {
                        if ((is_string($value) && substr($value, 0, 1) == '@') || (class_exists('CURLFile') && $value instanceof CURLFile)) {
                            $filepost = true;
                            break;
                        }
                    }
                    if (!$filepost) {
                        $post = http_build_query($post);
                    }
                }
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
            if (!empty($GLOBALS['_W']['config']['setting']['proxy'])) {
                $urls = parse_url($GLOBALS['_W']['config']['setting']['proxy']['host']);
                if (!empty($urls['host'])) {
                    curl_setopt($ch, CURLOPT_PROXY, "{$urls['host']}:{$urls['port']}");
                    $proxytype = 'CURLPROXY_' . strtoupper($urls['scheme']);
                    if (!empty($urls['scheme']) && defined($proxytype)) {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, constant($proxytype));
                    } else {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                    }
                    if (!empty($GLOBALS['_W']['config']['setting']['proxy']['auth'])) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['_W']['config']['setting']['proxy']['auth']);
                    }
                }
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            if (defined('CURL_SSLVERSION_TLSv1')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
            if (!empty($extra) && is_array($extra)) {
                $headers = array();
                foreach ($extra as $opt => $value) {
                    if (!strpos($opt, 'CURLOPT_')) {
                        curl_setopt($ch, constant($opt), $value);
                    } elseif (is_numeric($opt)) {
                        curl_setopt($ch, $opt, $value);
                    } else {
                        $headers[] = "{$opt}: {$value}";
                    }
                }
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
            }
            $data = curl_exec($ch);
            $status = curl_getinfo($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            if ($errno || empty($data)) {
                return error(1, $error);
            } else {
                return $this->ihttp_response_parse($data);
            }
        }
        $method = empty($post) ? 'GET' : 'POST';
        $fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
        $fdata .= "Host: {$urlset['host']}\r\n";
        if (function_exists('gzdecode')) {
            $fdata .= "Accept-Encoding: gzip, deflate\r\n";
        }
        $fdata .= "Connection: close\r\n";
        if (!empty($extra) && is_array($extra)) {
            foreach ($extra as $opt => $value) {
                if (strpos($opt, 'CURLOPT_')) {
                    $fdata .= "{$opt}: {$value}\r\n";
                }
            }
        }
        $body = '';
        if ($post) {
            if (is_array($post)) {
                $body = http_build_query($post);
            } else {
                $body = urlencode($post);
            }
            $fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
        } else {
            $fdata .= "\r\n";
        }
        if ($urlset['scheme'] == 'https') {
            $fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
        } else {
            $fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
        }
        stream_set_blocking($fp, true);
        stream_set_timeout($fp, $timeout);
        if (!$fp) {
            return error(1, $error);
        } else {
            fwrite($fp, $fdata);
            $content = '';
            while (!feof($fp))
                $content .= fgets($fp, 512);
            fclose($fp);
            return ihttp_response_parse($content, true);
        }
    }
    public function ihttp_response_parse($data, $chunked = false) {
        $rlt = array();
        $headermeta = explode('HTTP/', $data);
        if (count($headermeta) > 2) {
            $data = 'HTTP/' . array_pop($headermeta);
        }
        $pos = strpos($data, "\r\n\r\n");
        $split1[0] = substr($data, 0, $pos);
        $split1[1] = substr($data, $pos + 4, strlen($data));

        $split2 = explode("\r\n", $split1[0], 2);
        preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
        $rlt['code'] = $matches[2];
        $rlt['status'] = $matches[3];
        $rlt['responseline'] = $split2[0];
        $rlt['headers'] = array();
        $header = explode("\r\n", $split2[1]);

        //var_dump($header);die;
        $isgzip = false;
        $ischunk = false;
        foreach ($header as $v) {
            $pos = strpos($v, ':');
            $key = substr($v, 0, $pos);
            $value = trim(substr($v, $pos + 1));
            $rlt['headers'][$key] = array();
            if (is_array($rlt['headers'][$key])) {
                $rlt['headers'][$key][] = $value;
            } elseif (!empty($rlt['headers'][$key])) {
                $temp = $rlt['headers'][$key];
                unset($rlt['headers'][$key]);
                $rlt['headers'][$key][] = $temp;
                $rlt['headers'][$key][] = $value;
            } else {
                $rlt['headers'][$key] = $value;
            }
            if(!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
                $isgzip = true;
            }
            if(!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
                $ischunk = true;
            }
        }
        if($chunked && $ischunk) {
            $rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
        } else {
            $rlt['content'] = $split1[1];
        }
        if($isgzip && function_exists('gzdecode')) {
            $rlt['content'] = gzdecode($rlt['content']);
        }

        $rlt['meta'] = $data;
        if($rlt['code'] == '100') {
            return ihttp_response_parse($rlt['content']);
        }
        return $rlt;
    }
}