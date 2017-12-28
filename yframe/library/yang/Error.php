<?php
/**
 * Author: yangyang
 * Date  : 17-12-28
 * Time  : 上午10:18
 */

namespace yang;


class Error {

    static private $interface;
    public static function register() {
        if (empty(self::$interface)) {
            self::$interface = new static();
        }
        return self::$interface;
    }

    public function __construct()
    {
        if (App::$app_debug) {
            ini_set('display_errors', 'On');
            error_reporting(E_ALL);

            set_exception_handler([$this, 'exception']);
            set_error_handler([$this, 'error']);
            register_shutdown_function([$this, 'shutdown']);
        } else {
            ini_set('display_errors', 'Off');
        }
    }

    public function exception(\Exception $e) {

    }

    public function error($erron, $errstr, $errfile, $errline) {

    }

    public function shutdown() {

        if (!is_null($error = error_get_last()) && static::isFatal($error['type'])) {
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            self::exception($exception);
        }

    }

    private function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    public function output() {

    }
}