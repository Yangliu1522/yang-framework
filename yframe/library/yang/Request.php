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

    protected $mimeType = [
        'xml' => 'application/xml,text/xml,application/x-xml',
        'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js' => 'text/javascript,application/javascript,application/x-javascript',
        'css' => 'text/css',
        'rss' => 'application/rss+xml',
        'yaml' => 'application/x-yaml,text/yaml',
        'atom' => 'application/atom+xml',
        'pdf' => 'application/pdf',
        'text' => 'text/plain',
        'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
        'csv' => 'text/csv',
        'html' => 'text/html,application/xhtml+xml,*/*',
    ];
    private $pathinfo = '', $input, $post, $ip, $script, $root_url, $domain, $method = '', $suffix = '', $action, $module, $controller, $server, $get;
    /**
     * @return static
     */
    public static function create() {

        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        return self::$instrace;
    }

    public function __construct()
    {
        $this->input = file_get_contents('php://input');
        $this->server = $_SERVER;
    }

    /**
     * 当前URL的访问后缀
     * @access public
     * @return string
     */
    public function ext()
    {
        return pathinfo($this->pathinfo(), PATHINFO_EXTENSION);
    }

    /**
     * 获取pathinfo
     * @return mixed
     */
    public function pathinfo()
    {
        if (!empty($this->pathinfo)) {
            return $this->pathinfo;
        }
        if (!empty($this->server['PATH_INFO'])) {
            $this->pathinfo = trim($_SERVER['PATH_INFO'], '/');
        } else {
            if (Config::get('url_parse.query')) {
                $this->pathinfo = $_GET[Config::get('url_parse.query')];
            } elseif (!empty($this->server['QUERY_STRING'])) {
                $this->pathinfo = $this->server['QUERY_STRING'];
            }
        }
        $this->pathinfo = str_replace(Config::get('url_parse.url_esp'), '/', $this->pathinfo);
        return $this->pathinfo;
    }

    /**
     * 获取去掉后缀的path路由
     * @return string
     */
    public function path()
    {
        $url = $this->pathinfo();
        $suffix = $this->suffix();

        if (!empty($suffix)) {
            $url = str_replace($suffix, '', $url);
        }

        return trim($url, '/');
    }


    /**
     * 设置或者获取当前的Header
     * @access public
     * @param string|array $name header名称
     * @param string $default 默认值
     * @return string
     */
    public function header($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ? $this->server : $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }

    /**
     * @param $name
     * @param string $default
     * @param string $callback
     * @return array|mixed|string
     * @throws ErrorException
     */
    public function server($name = '', $default = '', $callback = '') {

        if (empty($this->server)) {
            $this->server = $_SERVER;
        }

        if (empty($name)) {
            return $this->server;
        }

        $name = strtoupper($name);
        return $this->filter($this->server, $name, $default, '',  $callback);
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

    /**
     * 判断请求方式
     * @param bool $method
     * @return string
     */
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
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public function isMobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 过滤
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

    /**
     * 获取或设置url
     * @param string $url
     * @return string
     */
    public function baseurl($url = '')
    {
        if (!empty($url)) {
            return '/' . trim('/' . $this->script() . '/' . $url, '/') . $this->suffix();
        }
        return $this->server('REQUEST_URI');
    }

    public function script()
    {
        if (empty($this->script)) {
            $script = $this->server('SCRIPT_NAME');
            $this->script = $script;
        }
        return $this->script;
    }

    /**
     * 设置伪静态后缀
     * @param string $suffix
     * @return bool
     */
    public function suffix($suffix = '')
    {

        if (!empty($suffix)) {
            $this->suffix = $suffix;
            return true;
        }

        if (!empty($this->suffix)) {
            if (is_array($this->suffix)) {
                $data = array_rand($this->suffix, 1);
                return $data[0];
            }
            return $this->suffix;
        }

        $suffix = Config::get('url_parse.url_html_suffix');
        if (strpos($suffix, '|')) {
            foreach ($suffix as &$val) {
                $val = '.' . $val;
            }
            $this->suffix = explode('|', $suffix);
        }

        $this->suffix = $suffix;
    }

    public function is_rewirte()
    {
        if (Config::get('use_rewirte')) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前域名
     * @return string
     */
    public function domain()
    {
        if (empty($this->domain)) {
            $url = 'http://';
            if ($this->isSsl()) {
                $url = 'https://';
            }
            $url .= $this->host();
            $this->domain = $url . '/';
        }
        return $this->domain;
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl()
    {
        $server = array_merge($_SERVER, $this->server);
        if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
            return true;
        } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
            return true;
        } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
            return true;
        } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
            return true;
        } elseif (Config::get('https_agent_name') && isset($server[Config::get('https_agent_name')])) {
            return true;
        }
        return false;
    }

    /**
     * 获取域名
     * @return bool
     */
    public function host()
    {
        if (isset($_SERVER['HTTP_X_REAL_HOST'])) {
            return $_SERVER['HTTP_X_REAL_HOST'];
        }
        return $this->server('HTTP_HOST');
    }

    /**
     * 设置或获取当前的controller
     * @param string $name
     */
    public function controller($name = '')
    {
        if (empty($name)) {
            return $this->controller;
        }
        $this->controller = $name;
    }

    /**
     * 设置或获取当前的module
     * @param string $name
     */
    public function module($name = '')
    {
        if (empty($name)) {
            return $this->module;
        }
        $this->module = $name;
    }

    /**
     * 设置或获取当前的action
     * @param string $name
     */
    public function action($name = '')
    {
        if (empty($name)) {
            return $this->action;
        }
        $this->action = $name;
    }

    /**
     * 未修正
     * 获取指定路由名字
     * @param string $url
     * @param array $args
     */
    public function route($url = '', $args = [])
    {
        return Route::Instrace()->search($url, $args);
    }

    /**
     * 读取设置和删除session
     * 如果name为空返回全部session
     * @param string $name session名
     * @param string $value session值
     * @return bool
     */
    public function session($name = '', $value = '')
    {
        if (!empty($name)) {
            if (empty($value) && !is_null($value)) {
                return Session::instrace()->get($name);
            }

            if (!empty($value)) {
                Session::instrace()->set($name, $value);
                return true;
            }

            if (is_null($value)) {
                Session::instrace()->del($name);
                return true;
            }
        }

        return Session::instrace()->all();
    }
}