<?php
/**
 * Author: yangyang
 * Date  : 17-12-29
 * Time  : 下午7:18
 */

namespace yang\logs;


interface LogServe
{

    public static function init();

    public function save($content = '');

    public function clear();
}