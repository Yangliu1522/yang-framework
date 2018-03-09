<?php
/**
 * Created by PhpStorm.
 * User: smy
 * Date: 18-3-2
 * Time: 下午6:05
 */

namespace yang\exception;


class FatalException extends \Exception
{
    public $type;

    public function __construct($message = "", $code = 0, $file = '', $line = 0, \Throwable $previous = null)
    {
        if (empty($file)) {
            $debug = debug_backtrace()[0];
            $file = $debug['file'];
            $line = $debug['line'];
        }

        $this->file = $file;
        $this->line = $line;

        parent::__construct($message, $code, $previous);
    }
}