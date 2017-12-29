<?php
/**
 * Author: yangyang
 * Date  : 17-12-25
 * Time  : 下午6:17
 */

namespace yang;

/**
 * Class Env
 * @package yang
 * 环境变量控制 不再使用常量作为环境变量
 */
class Env
{
    /**
     * 设置环境变量
     * @param string $name
     * @param string $value
     */
    static public function set($name, $value) {
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
    static public function get($name) {
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
    static public function del($name) {
        $name = strtoupper($name);
        if (isset($_ENV[$name])) {
            unset($_ENV[$name]);
        }
        putenv($name . '=');
    }

    /**
     * 从数组里添加到环境变量
     * @param array $data
     */
    static public function setArray(array $data) {
        foreach ($data as $key => $val) {
            self::set($key, $val);
        }
    }
}