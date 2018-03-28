<?php
/**
 * Author: yangyang
 * Date  : 18-1-3
 * Time  : 下午4:17
 * Name  : 路由类, 监听与注册路由
 */

namespace yang;

use yang\exception\RouteException;

class Route
{
    static private $register = [];
    /**
     * @var static
     */
    static private $instrace;
    /**
     * @var \yang\Request
     */
    static private $request;

    public function __construct()
    {
    }

    /**
     * 创建路由实例化, 并且批量注册路由
     * @param array $route
     * @param Request $request
     * @return static
     */
    public function create(array $route, Request $request)
    {
        self::$request = $request;
        $this->register($route);
        return $this;
    }

    /**
     * 注册路由
     * @param array $route
     */
    public function register(array $route)
    {

        foreach ($route as $key => $value) {
            if (is_array($value)) {
                $this->parse_route($key, $value[0], $value[1], $value[2]);
                continue;
            }
            $this->parse_route_array($key, $value);
        }
    }

    /**
     * 普通注册路由
     * @param $route
     * @param $callBack
     * @param string $method
     * @param array $parmes
     */
    private function parse_route($route, $callBack, $method = 'ANY', $parmes = [])
    {
        $rend = [];
        $route = explode('/', $route);
        $route_bak = $route;
        foreach ($route_bak as $vl) {
            if (strpos($vl, '{') === 0) {
                $vl = trim($vl, '{}');
                if (strpos($vl, '?') === 0) {
                    $vl = trim($vl, '?');
                    $rend[$vl] = [
                        'shadow' => true
                    ];
                } else {
                    $rend[$vl] = [];
                }

                if (!empty($parmes)) {
                    $rend[$vl]['reg'] = $parmes[$vl];
                    array_pop($parmes);
                } else {
                    $rend[$vl]['reg'] = '.*';
                }
                array_pop($route);
            }
        }

        if (is_string($method)) {
            $method = strtoupper($method);
            if (strpos($method, ',')) {
                $method = explode(',', $method);
            } else {
                $method = [$method];
            }
        }

        if (count($route) > 1) {
            self::$register[array_shift($route)][implode('/', $route)] = [
                'callback' => $callBack,
                'params' => $rend,
                'method' => $method
            ];
        } else {
            self::$register[implode('/', $route)]['__SELF__'] = [
                'callback' => $callBack,
                'params' => $rend,
                'method' => $method
            ];
        }
    }

    /**
     * 批量注册路由
     * @param $key
     * @param $routeArray
     */
    private function parse_route_array($key, $routeArray)
    {
        $key = trim($key, '[]');
        foreach ($routeArray as $k => $val) {
            $k = $key . '/' . $k;
            $this->parse_route($k, $val[0], $val[1], $val[2]);
        }
    }

    /**
     * 通用格式路由
     * @param $route
     * @param $callback
     * @param array $params
     */
    public static function Any($route, $callback, $params = [])
    {
        self::$instrace->parse_route($route, $callback, 'ANY', $params);
    }

    /**
     * GET请求的路由
     * @param $route
     * @param $callback
     * @param array $params
     */
    public static function Get($route, $callback, $params = [])
    {
        self::$instrace->parse_route($route, $callback, 'GET', $params);
    }

    /**
     * POST请求的路由
     * @param $route
     * @param $callback
     * @param array $params
     */
    public static function Post($route, $callback, $params = [])
    {
        self::$instrace->parse_route($route, $callback, 'POST', $params);
    }

    /**
     * AJAX请求的路由
     * @param $route
     * @param $callback
     * @param array $params
     */
    public static function Ajax($route, $callback, $params = [])
    {
        self::$instrace->parse_route($route, $callback, 'AJAX', $params);
    }

    /**
     * PUT请求的路由
     * @param $route
     * @param $callback
     * @param array $params
     */
    public static function Put($route, $callback, $params = [])
    {
        self::$instrace->parse_route($route, $callback, 'PUT', $params);
    }

    /**
     * 监听路由
     * @throws RouteException
     */
    public function listen($base = '')
    {
        $url = self::$request->path();
        if (empty($url)) {
            $url = $base;
        }
        krsort(self::$register);
        foreach (self::$register as $base => $child) {
            $child = array_reverse($child);
            foreach ($child as $key => $value) {
                if ($key == '__SELF__') {
                    $key = $base;
                } else {
                    $key = $base . '/' . $key;
                }
                if (strpos($url . '/', $key . '/') === 0) {
                    if (isset($value['callback'])) {
                        // $controller = $value['callback'];
                        $params = ltrim($url, $key . '/');
                        $method = $value['method'];
                        if ($method[0] != 'ANY' && !in_array(self::$request->method(), $method)) {

                            Log::recore('HTTP', 'Request method is not really');
                            if (Common::$app_debug) {
                                throw new RouteException('请求方式错误');
                            } else {
                                // 此处抛出404;
                                die;
                            }
                        }

                        $this->parse_params($params, $value['params']);
                        return $this->run(explode('/', trim($value['callback'], '/')));
                        break;
                    }
                    foreach ($value as $k2 => $v2) {
                        if (strpos($url, $key . '/' . $k2 . '/') === 0) {
                            break;
                        }
                    }
                }
            }
        }

        $route = explode('/', $url);
        $callback = array_splice($route, 0,3);
        // 假如路由不完整时, 如下操作
        if (count($callback) < 3) {
            $base = explode('/', $base);
            $callback = array_reverse($callback);
            for ($i = 2; $i >= 0; $i --) {
                $base[$i] = array_shift($callback);
                if (empty($callback)) break;
            }
            $callback = $base;
        }
        if (!empty($route)) {
            $this->parse_params($route, []);
        }
        return $this->run($callback);
    }

    private function run($callback) {
        Request::create()->module($callback[0]);
        Request::create()->action(end($callback));
        Request::create()->controller($callback[1]);

        if (is_string($callback)) {
            $callback = explode('/', $callback);
        }

        array_splice($callback,0,0,Env::get('app_name'));
        array_splice($callback,2,0,'controller');

        $func = array_pop($callback);
        $class = implode('\\', $callback);
        return (new $class())->$func();
    }

    /**
     * @param $array
     * @param array $params
     * @throws RouteException
     */
    private function parse_params($array, $params = []) {
        if (strpos($array, '/') !== false) {
            $route_params = explode('/', trim($array, '/'));
        } else {
            $route_params = [$array];
        }

        if (!empty($params)) {
            foreach ($params as $key => $reg) {
                $check = array_shift($route_params);
                if (!empty($check) && preg_match('/' . $reg['reg']. '/', $check)) {
                    self::$request->get($key, $check);
                } else {
                    if (!isset($reg['shadow'])){
                        Log::recore('ARGV', $key . ' type error or not found');
                        throw new RouteException('参数错误');
                    }
                }
            }
        }

        if (!empty($route_params)) {
            for($i = 0; $i < count($route_params) + 1; $i += 2) {
                if (!isset($route_params[$i + 1])) {
                    $route_params[$i + 1] = '';
                }
                self::$request->get($route_params[$i],$route_params[$i + 1]);
            }
        }
    }
}