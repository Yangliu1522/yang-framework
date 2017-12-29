<?php
/**
 * Author: yangyang
 * Date  : 17-12-29
 * Time  : 下午7:14
 */

namespace yang;


class Debug
{
    public static function create($name, $value) {
        return new static();
    }

    public function __construct()
    {

    }
}