<?php
/**
 * Author: yangyang
 * Date  : 17-12-23
 * Time  : 下午3:43
 */

// 这里是 开始文件
$startMem = memory_get_usage();
$start = microtime(true);
// 表示系统路径分隔符
$ds = DIRECTORY_SEPARATOR;
$root_dir = dirname(__FILE__) . $ds;

require $root_dir . 'library/yang/Loader.php';
// 注册自动加载
\yang\Loader::base();
// 开始写配置, 我们要注册array甚至多个

$config = []; // 初始化声明

$config['yf_start_time'] = $start;
$config['yf_start_mem'] = $startMem;
$config['root_path']     = $root_dir; // 根路径 就是Yframe这个文件夹所在的位置
$config['base_path']     = dirname($root_dir);// 根路径的上一级路径
$config['vender_path']   = $config['base_path'] . $ds . 'vendor' . $ds;
// 公共路径
// 这个代表app所在的,也就是入口的路径
$config['app_path'] = dirname($_SERVER['SCRIPT_FILENAME']) . $ds;
$config['control_path'] = $config['app_path'] . 'applications' . $ds;
$config['runtime_path'] = $config['app_path'] . 'runtime' . $ds;
$config['cache_path'] = $config['runtime_path'] . 'cache' . $ds;
$config['log_apth'] = $config['runtime_path'] . 'log' . $ds;
$config['tpl_cache_path'] = $config['runtime_path'] . 'template' . $ds;
// 批量注册系统常量
\yang\Env::setArray($config);
// 公共设置
$config = include 'common/config/config.php';
\yang\Env::setArray($config);
// 这里是应用开始
\yang\Error::register();
\yang\Loader::deep();
\yang\App::create();