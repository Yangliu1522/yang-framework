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
        if (App::$app_debug === true) {
            // 设置php.ini里的内容 这里是打开错误提示
            ini_set('display_errors', 'On');
            // 设置显示的错误级别 E_ALL 就是全部 E_ALL&~E_WARNING 这样就是显示除了warning的错误
            error_reporting(E_ALL);// 忘了打开
        } else {
            ini_set('display_errors', 'Off');
        }
        // 设置出现异常事调用的函数
        set_exception_handler([$this, 'exception']);
        // 设置出现错误时调用的函数
        set_error_handler([$this, 'error']);
        // 设置程序结束后执行的错误
        register_shutdown_function([$this, 'shutdown']);
    }

    public function exception($e) {
        $this->output($e); // 这里显示 所有的异常都最终指向这个方法
    }

    /**
     * @param $erron 错误码
     * @param $errstr 错误信息
     * @param $errfile 错误所在文件
     * @param $errline 错误在文件的哪一行
     */
    public function error($erron, $errstr, $errfile, $errline) {
        $exception = new ErrorException($errstr, $erron, $errfile, $errline);
        $this->exception($exception);
    }

    public function shutdown() {

        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $exception = new ErrorException($error['message'], $error['type'], $error['file'], $error['line']);
            $this->exception($exception);
        }
        Log::save();
    }

    private function isFatal($type)
    {
        // 判断是不是致命错误 致命错误会提前终止程序,但是会调用shutdown
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    // 输出错误 可以尝试各种错误
    public function output($e) {
        // 输出错误信息
        echo '错误信息是 ' . $e->getMessage() . '<br>';
        // 错误码
        echo '错误代码是 ' . $e->getCode() . '<br>';
        // 错误所在文件
        echo '错误文件是 ' . $e->getFile() . '<br>';
        // 错误在文件哪一行
        echo '错误位置是 ' .$e->getLine() . '<br>';
        // 详细的信息
        echo '更多详细内容 ' . $e->getTraceAsString() . '<br>';
    }
}