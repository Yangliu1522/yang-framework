<?php
/**
 * Author: yangyang
 * Date  : 18-1-3
 * Time  : 下午4:17
 * Name  : 路由类, 监听与注册路由
 */

namespace yang;

class Route
{
    static private $register = [], $instrace, $request;

    public function __construct()
    {
        return $this;
    }

    /**
     * 创建路由实例化, 并且批量注册路由
     * @param array $route
     * @param Request $request
     */
    public static function create(array $route, Request $request)
    {
        self::$request = $request;
        if (empty(self::$instrace)) {
            self::$instrace = new static();
            self::$instrace->register($route);
        }
    }

    /**
     * 注册路由
     * @param array $route
     */
    public function register(array $route)
    {

        foreach ($route as $key => $value) {
            if (!is_array($value[0])) {
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
        $route_bak = array_reverse($route);

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
                    $rend[$vl]['reg'] = array_pop($parmes);
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
            self::$register[implode('/', $route)] = [
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

    public function listen()
    {
        $url = self::$request->path();

        foreach (self::$register as $key => $value) {
            if (strpos($url, $key) === 0) {
                if (isset($value['callback'])) {
                    break;
                }
                foreach ($value as $k2 => $v2) {
                    if (strpos($url, $key . '/' . $k2) === 0) {
                        break;
                    }
                }
            }
        }
    }
}