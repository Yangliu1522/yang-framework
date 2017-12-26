<?php
/**
 * Author: yangyang
 * Date  : 17-12-23
 * Time  : 下午3:43
 */

// 这里是 开始文件
$start = microtime(true);
$root_dir = dirname(__FILE__);

require $root_dir . '/../vendor/autoload.php';

\yang\App::create();