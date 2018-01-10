<?php
/**
 * Author: yangyang
 * Date  : 18-1-3
 * Time  : 下午4:04
 */

namespace yang;

// 禁止类内实现引入功能
class Config
{
    static private $configs = [];

    public static function get($name)
    {
        self::parseName($name);
        if (!is_array($name)) {
            return isset(self::$configs[$name]) ? self::$configs[$name] : null;
        } else {
            $parse = [];
            foreach ($name as $val) {
                if (!isset(self::$configs[$val])) {
                    return null;
                } else {
                    $parse = self::$configs[$val];
                }

                if (!isset($parse[$val])) {
                    return null;
                }
                $parse = $parse[$val];
            }
            return $parse;
        }
    }

    public static function set($name, $config)
    {
        self::parseName($name);

        if (!is_array($name)) {
            self::$configs[$name] = $config;
        } else {
            if (count($name) === 2) {
                self::$configs[$name[0]][$name[1]] = $config;
            } else {
                self::$configs[$name[0]][$name[1]][$name[2]] = $config;
            }
        }
    }

    public static function setAsArray($configs)
    {
        foreach ($configs as $key => $val) {
            self::set($key, $val);
        }
    }

    private static function parseName(&$name)
    {
        $name = trim($name, '.');
        if (strpos($name, '.') != false && substr_count($name, '.') < 3) {
            $name = explode('.', $name, 3);
        }

        return $name;
    }
}