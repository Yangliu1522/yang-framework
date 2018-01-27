<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 18-1-4
 * Time: 下午4:29
 */

namespace yang\caches;


interface CacheServer
{
    /**
     * 添加缓存接口
     * @param string $name
     * @param $value
     */
    public function add($name, $value);

    /**
     * 删除缓存接口
     * @param string $name
     * @return number 返回删除缓存的数量
     */
    public function del($name);

    /**
     * 清除所有缓存或指定名称的缓存
     * @param string $name
     */
    public function clear($name = '');

    /**
     * 初始化, 重置
     * @return static
     */
    public static function init();
}