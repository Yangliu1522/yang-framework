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
        self::$app_debug = Env::get('app_debug');
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
        Loader::deep();

        Loader::base()->setPsr4(Env::get('app_name'), Env::get('app_path'));
        echo '开始了';
    }
}