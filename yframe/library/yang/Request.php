<?php
/**
 * Author: yangyang
 * Date  : 17-12-29
 * Time  : 下午10:33
 */

namespace yang;


class Request
{
    static private $instrace;

    private $input, $server, $post, $method;

    public static function create() {

        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        return self::$instrace;
    }

    public function __construct()
    {
        $this->input = file_get_contents('php://input');
    }

    // 获取$_SERVER

    /**
     * @param $name
     * @param string $default
     * @param string $callback
     * @return array|mixed|string
     * @throws ErrorException
     */
    public function server($name, $default = '', $callback = '') {

        if (empty($this->server)) {
            $this->server = $_SERVER;
        }

        $name = strtoupper($name);
        return $this->filter($this->server, $name, $default, '',  $callback);
    }

    public function baseurl() {

    }

    /**
     * @param $name
     * @param string $value
     * @param string $default
     * @param string $callback
     * @return array|mixed|string
     * @throws ErrorException
     */
    public function post($name, $value = '', $default = 's', $callback = '') {

        if (empty($this->post)) {
            parse_str($this->input, $post);
            $this->post = $post;
        }

        if (empty($value)) {
            return $this->filter($this->post, $name, $default, $callback);
        }
        $this->post[$name] = $value;
    }


    /**
     * @param $name
     * @param string $value
     * @param string $default
     * @param string $callback
     * @return array|mixed|string
     * @throws ErrorException
     */
    public function get($name, $value = '', $default = 's', $callback = '') {
        if (empty($this->get)) {
            $this->get = $_GET;
        }

        if (empty($value)) {
            return $this->filter($this->get, $name, null, $callback);
        }
        $this->get[$name] = $value;
    }

    public function method($method = false) {
        if (true === $method) {
            // 获取原始请求类型
            return isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
        } elseif (!$this->method) {
            if (isset($this->post[Env::get('var_method')])) {
                $this->method = strtoupper($this->post[Env::get('var_method')]);
                $this->{$this->method}($this->post);
            } elseif (isset($this->server['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->method = strtoupper($this->server['HTTP_X_HTTP_METHOD_OVERRIDE']);
            } else {
                $this->method = isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : $this->server['REQUEST_METHOD'];
            }
        }
        return $this->method;
    }

    /**
     * 判断是否是post请求
     * @return bool
     */
    public function isPost() {
        return $this->method() == 'POST';
    }

    /**
     * 判断是否是get请求
     * @return bool
     */
    public function isGet() {
        return $this->method() == 'GET';
    }

    /**
     * 判断是否是put请求
     * @return bool
     */
    public function isPut() {
        return $this->method() == 'PUT';
    }

    /**
     * 判断是否是delect请求
     * @return bool
     */
    public function isDelect() {
        return $this->method() == 'DELECT';
    }

    /**
     * 当前是否Ajax请求
     * @access public
     * @param bool $ajax  true 获取原始ajax请求
     * @return bool
     * @throws ErrorException
     */
    public function isAjax($ajax = false)
    {
        $value  = $this->server('HTTP_X_REQUESTED_WITH', '', 'strtolower');
        $result = ('xmlhttprequest' == $value) ? true : false;
        if (true === $ajax) {
            return $result;
        } else {
            return $this->post(Env::get('var_ajax')) ? true : $result;
        }
    }

    /**
     * 当前是否Pjax请求
     * @access public
     * @param bool $pjax  true 获取原始pjax请求
     * @return bool
     * @throws ErrorException
     */
    public function isPjax($pjax = false)
    {
        $result = !is_null($this->server('HTTP_X_PJAX')) ? true : false;
        if (true === $pjax) {
            return $result;
        } else {
            return $this->post(Env::get('var_pjax')) ? true : $result;
        }
    }

    /**
     * 获取客户端IP地址
     * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean   $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public function ip($type = 0, $adv = false)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($this->server['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($this->server['HTTP_CLIENT_IP'])) {
                $ip = $this->server['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $this->server['REMOTE_ADDR'];
            }
        } elseif (isset($this->server['REMOTE_ADDR'])) {
            $ip = $this->server['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }

    /**
     * @param array $data
     * @param string $name
     * @param null $default
     * @param string $callback
     * @param string $type
     * @return array|mixed|string
     * @throws ErrorException
     */
    public function filter(array $data, $name = '', $default = null, $callback = '', $type = 's') {
        if (strpos($name, ':')) {
            $type = explode(':', $name);
            $name = $type[0];
            $type = end($type);
        }
        $value = isset($data[$name]) ? $data[$name] : $default;
        if (empty($callback)) {
            return $this->filterCallbakc($value, $type);
        }
        return call_user_func([$this, $callback], $value);
    }

    /**
     * 过滤并转换
     * @param array $data
     * @param string $name
     * @param string $type
     * @throws ErrorException
     */
    public function filterCallbakc($value, $type = 's') {

        if (!empty($value)) {
            if (!isset($value)) {
                return false;
            }
            switch ($type) {
                case 'j':
                    return json_encode($value);
                case 'o':
                    return json_decode($value);
                case 'a':
                    return (array) $value;
                case 's':
                    return (string) $value;
                default:
                    throw new ErrorException(gettype($value) . ' Not Found This Types ');
            }
        }
        return $value;
    }
}