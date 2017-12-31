<?php
/**
 * Author: yangyang
 * Date  : 17-12-29
 * Time  : 下午7:16
 */

namespace yang;


class Log
{
    static private $serve;
    private static $logs = [];
    private static $type = [
        'log', 'notice', 'error', 'critical', 'alert', 'debug', 'warning', 'emergency'
    ];
    private static function create() {
        if (empty(self::$serve)) {
            switch (Env::get('log_type')) {
                case 'file':
                default:
                    self::$serve = \yang\logs\File::init();
            }
        }
        return self::$serve;
    }

    public static function recore($name, $value = '', $type = 'log')
    {
        if (!in_array($type, self::$type)) {
            $type = 'log';
        }
        $type = strtoupper($type);
        $value = self::convertArray($value);
        $str = "[{$type}][{$name}] {$value}". PHP_EOL;
        self::$logs[] = $str;
    }

    public static function save() {
        $str = '----------------------------------------' . PHP_EOL;
        $str = implode('', self::$logs) . $str . PHP_EOL;
        self::create()->save($str);
        self::$logs = [];
    }

    public static function write($name, $value = '', $type = 'info') {
        self::recore($name, $value, $type);
        self::save();
    }

    public static function convertArray($value) {
        if (is_array($value)) {
            return json_encode($value);
        }
        return $value;
    }
}