<?php
/**
 * Author: yangyang
 * Date  : 17-12-25
 * Time  : 下午6:17
 */

namespace yang;


class Env
{
    public static $instrace;
    public function __construct()
    {

    }

    /**
     * 初始化Env
     * @return static
     */
    static public function run() {
        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        return self::$instrace;
    }

    /**
     * 设置环境变量
     * @param string $name
     * @param string $value
     */
    public function set($name, $value) {
        if (!empty($value)) {
            $name = strtoupper($name);
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
        }
    }

    /**
     * 获取环境变量
     * @param string $name
     * @return array|false|string
     */
    public function get($name) {
        $name = strtoupper($name);
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }
        return getenv($name);
    }

    /**
     * 删除环境变量
     * @param string $name
     */
    public function del($name) {
        $name = strtoupper($name);
        if (isset($_ENV[$name])) {
            unset($_ENV[$name]);
        }
        putenv($name . '=');
    }

    /**
     * 静态调用
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$instrace, $name], $arguments);
    }
}