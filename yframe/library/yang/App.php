<?php
/**
 * Author: yangyang
 * Date  : 17-12-23
 * Time  : 下午3:46
 */

namespace yang;

/*
 * 入口控制
 */
class App
{
    // 调用App的内置公共函数
    public static $instrace;

    private static $request, $route;
    public static $app_debug = true;

    /**
     * 创建基础结构
     */
    public static function create(Request $request = null)
    {
        // self::$app_debug = Env::get('app_debug');
        Fastload::includeFile(Env::get('root_path') . 'helper.php');
        Log::recore('DATE', date('Y-m-d H:i:s', time()));
        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        self::$request = $request !== null ? $request : Request::create();
        self::$route = Route::create([], self::$request);
        // self::$instrace->start(); 测试一下
    }

    /**
     * 创建基础目录结构
     */
    private static function createBase() {
    }

    public static function path2url($path) {
        $root2 = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $base2 = str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['DOCUMENT_ROOT']);
        return str_replace($base2, '', $root2);
    }
    /**
     * 监听应用
     */
    public static function listen()
    {
        Fastload::includeFile(Env::get('app_path') . 'helper.php');
        Fastload::add(Env::get('app_name') . "\\", Env::get('app_path'));
        ob_start();
        $data = self::$route->listen('index/index/index');
        // 请求完毕
        // App::dump($data);
        self::send($data);
        if (self::$app_debug) {
            Debug::create('end', 'run end');
        }
    }

    /**
     * 发送消息
     * @param $data
     */
    private static function send($data) {
        if (is_a($data, __NAMESPACE__ . '\\Response')) {
            $data->send();
        } else {
            Response::create($data, 200)->send();
        }
    }

    /**
     * @param mixed
     */
    public static function dump()
    {
        $str = func_get_args();
        foreach ($str as $arrrorstring) {
            // $arrrorstring = htmlspecialchars($arrrorstring);
            $pre = print_r($arrrorstring, true);
            echo '<p><pre>' . $pre . '</pre></p>' . PHP_EOL;
        }
    }
}