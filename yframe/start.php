<?php
/**
 * Author: yangyang
 * Date  : 17-12-23
 * Time  : 下午3:43
 */

// 这里是 开始文件
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
$config['root_path']     = $root_dir;
$config['base_path']     = dirname($root_dir);
$config['vender_path']   = $config['base_path'] . $ds . 'vendor' . $ds;
// 公共路径
$config['app_path'] = dirname($_SERVER['DOCUMENT_ROOT']) . $ds;
$config['control_path'] = $config['app_path'] . 'applications' . $ds;
$config['runtime_path'] = $config['app_path'] . 'runtime' . $ds;
$config['cache_path'] = $config['runtime_path'] . 'cache' . $ds;
$config['tpl_cache_path'] = $config['runtime_path'] . 'template' . $ds;
\yang\Env::setArray($config);
// 公共设置
$config = include 'common/config.php';
\yang\Env::setArray($config);
// 这里是应用开始
\yang\Error::register();
\yang\App::create();