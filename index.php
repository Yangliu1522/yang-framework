<?php
/**
 * Author: yangyang
 * Date  : 17-12-23
 * Time  : 下午3:42
 */

// this is start file
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require 'yframe/start.php';
echo date('Y-m-d H:i:s', time());

\yang\App::listen();