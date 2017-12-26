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

    static public function run() {
        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        return self::$instrace;
    }

    public function set($name, $value) {
        if (!empty($value)) {
            $name = strtoupper($name);
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    public function get($name) {
        $name = strtoupper($name);

    }
}