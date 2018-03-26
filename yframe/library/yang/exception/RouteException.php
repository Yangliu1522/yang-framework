<?php
/**
 * Created by PhpStorm.
 * User: xmy
 * Date: 18-3-16
 * Time: 下午4:57
 */

namespace yang\exception;


class RouteException extends \RuntimeException
{
    public function __construct($message = "Route Error", $code = 0, $file = '', $line = 0)
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