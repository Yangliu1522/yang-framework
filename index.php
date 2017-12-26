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

// $app = new \yang\App();

$app = 'Hello';

$_ENV['APP'] = $app;

$start = microtime(true);

echo $app;

$end = microtime(true);
$apptime = $end - $start;

$start = microtime(true);

echo $_ENV['APP'];

$end = microtime(true);
$envtime = $start - $end;

if ($apptime > $envtime) {
    echo '$env';
} else {
    echo '$app';
}

\yang\App::listen();