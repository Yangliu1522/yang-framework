<?php
/**
 * Created by PhpStorm.
 * User: xmy
 * Date: 18-3-10
 * Time: 上午11:09
 */

namespace yang\exception;

//
class Premission extends \RuntimeException
{
    public $type;

    public function __construct($filename = "", $code = 0, $file = '', $line = 0)
    {
        $message = ' Permission denied ' . $filename;
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