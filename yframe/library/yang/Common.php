<?php
/**
 * Created by PhpStorm.
 * User: y_yang
 * Date: 18-1-26
 * Time: 上午10:36
 */

namespace yang;


class Common
{
    public static $app_debug = true;

    public static function updateDebug($debug) {
        static::$app_debug = $debug;
    }

    public static function path2url($path) {
        $root2 = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $base2 = str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['DOCUMENT_ROOT']);
        return str_replace($base2, '', $root2);
    }

    /**
     * @param mixed
     */
    public static function dump()
    {
        $str = func_get_args();
        foreach ($str as $arrrorstring) {
            // $arrrorstring = htmlspecialchars($arrrorstring);
            $pre = print_r($arrrorstring, true);
            echo '<p><pre>' . $pre . '</pre></p>' . PHP_EOL;
        }
    }

    static function fastcgi_finish_request()
    {
        ignore_user_abort(true);            // 客户端关闭程序继续执行
        header('X-Accel-Buffering: no');    // nginx 不缓存输出
        header("Connection: close");
        ob_end_flush();
        flush();
    }

    static function http_response_code($code = NULL)
    {
        if (function_exists('http_response_code')) {
            return http_response_code($code);
        }
        if ($code !== NULL) {
            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;

        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }
        return $code;
    }

    // 微擎函数

    public static function wurl($url, $extentd = '') {
        global $_W;
        $query = [];
        $params = array_merge($query, array(
            'm' => strtolower(Env::get("modulename")),
            'uniacid' => $_W['uniacid'],
            'c'  => 'site',
            'a'  => 'entry'
        ));
        if (!empty($url)) {
            $params['do'] = $url;
        }
        $url = "./index.php?";
        $queryString = http_build_query($params, '', '&');
        $url .= $queryString . '&' . $extentd;
        return $url;
    }

    // 微擎函数

    public static function murl($murl, $extentd = '', $noredirect = false) {
        global $_W;
        $url = '/app/';
        $query = [];
        $params = array_merge($query, array(
            'm' => strtolower(Env::get("modulename")),
            'i' => $_W['uniacid'],
            'c'  => 'entry'
        ));
        if (!empty($murl)) {
            $params['do'] = $murl;
        }
        $url .= "index.php?";
        $queryString = http_build_query($params, '', '&');
        $url .= $queryString. '&' . $extentd;
        if ($noredirect === false) {
            $url .= '&wxref=mp.weixin.qq.com#wechat_redirect';
        }
        return $url;
    }

    public static function humpToDot($str){
        $str = lcfirst($str);
        if (preg_match('/([A-Z]{1})/',$str)){
            $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
                return '.'.strtolower($matches[0]);
            },$str);
        }
        return $str;
    }

    /**
     * @param $callback
     * @return \yang\Model
     */
    public static function model($callback) {
        $callback = explode('/', trim($callback, '/'));

        array_splice($callback,0,0,Env::get('app_name'));
        array_splice($callback,2,0,'model');

        $class = implode('\\', $callback);
        return new $class;
    }

    /**
     * @param $callback
     * @return \yang\Model
     */
    public static function table($callback) {
        return Db::table($callback);
    }
}