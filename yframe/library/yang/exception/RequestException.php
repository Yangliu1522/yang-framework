<?php
/**
 * Created by PhpStorm.
 * User: xmy
 * Date: 18-3-27
 * Time: 下午3:46
 */

namespace yang\exception;


class RequestException extends \RuntimeException
{
    public function __construct($message = "Request Error", $code = 0, $file = '', $line = 0)
    {
        if (empty($file)) {
            $debug = debug_backtrace()[0];
            $file = $debug['file'];
            $line = $debug['line'];
        }

        $this->file = $file;
        $this->line = $line;

        parent::__construct($message, $code);
    }
}