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
    private static $instrace;

    /**
     * 创建基础结构
     */
    public static function create() {
        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        echo 'Start';
    }

    /**
     * 万物之根本 初始化
     * App constructor.
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * 监听应用
     */
    public static function listen() {
        echo '开始了';
    }
}