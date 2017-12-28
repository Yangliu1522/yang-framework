<?php
/**
 * Author: yangyang
 * Date  : 17-12-28
 * Time  : 上午10:29
 */

namespace yang;


use Throwable;

class ErrorException extends \Exception
{
    public $type;

    public function __construct($type = '',$message = "", $code = 0, $file = '', $line = 0, Throwable $previous = null)
    {
        if (empty($file)) {
            $debug = debug_backtrace()[0];
            $file = $debug['file'];
            $line = $debug['line'];
        }

        $this->type = '异常';


        parent::__construct($message, $code, $previous);
    }

}