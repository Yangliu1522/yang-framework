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

\yang\Log::recore('app', 'test');
\yang\App::listen();

