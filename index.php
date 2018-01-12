<?php
/**
 * Author: yangyang
 * Date  : 17-12-23
 * Time  : 下午3:42
 */
 ini_set('display_errors', 'On');
 error_reporting(E_ALL);
// 就是这里
require 'yframe/start.php';
\yang\Env::set('app_path', dirname(__FILE__) . '/applications/');
// 开始执行
\yang\App::listen();

