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
        'info', 'trace', 'exception', 'debug', 'warning'
    ];
    public static function create() {
        if (empty(self::$serve)) {
            switch (Env::get('log_type')) {
                case 'file':
                default:
                    self::$serve = \yang\logs\File::init();
            }
        }
        return self::$serve;
    }

    public static function recore($name, $value = '', $type = 'info')
    {
        if (!in_array($type, self::$type)) {
            $type = 'debug';
        }
        $type = strtoupper($type);
        $value = self::convertArray($value);
        $str = "[{$type}][{$name}] {$value}\n";
        self::$logs[] = $str;
    }

    public static function save() {
        self::create();
    }

    public static function write() {

    }

    public static function convertArray($value) {
        if (is_array($value)) {
            return json_encode($value);
        }
        return $value;
    }
}