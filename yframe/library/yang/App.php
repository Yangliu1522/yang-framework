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

    public static $app_debug = true;

    /**
     * 创建基础结构
     */
    public static function create() {
        // self::$app_debug = Env::get('app_debug');
        Log::recore('DATE', date('Y-m-d H:i:s', time()));
        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        echo 'Start';
        // self::$instrace->start(); 测试一下
    }
    /**
     * 监听应用
     */
    public static function listen() {
        Loader::base()->setPsr4(Env::get('app_name') . "\\", Env::get('app_path'));
        echo '开始了';



        // 请求完毕
        if (self::$app_debug) {
            Debug::create('end', 'run end');
        }
    }
}