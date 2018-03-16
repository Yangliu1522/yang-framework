<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 18-1-4
 * Time: 下午4:28
 */

namespace yang;


class Cache
{
    /**
     * @var caches\File;
     */
    private static $engine;
    private static function init() {
        if (empty(static::$engine)) {
            self::setEngine(Env::get('cache_engine'));
        }
    }

    private static function setEngine($name) {
        switch ($name) {
            case 'file':
            default:
                static::$engine = new caches\File();
        }
    }

    /**
     * @param $name
     * @param $value
     * @throws exception\Premission
     */
    public static function set($name, $value) {
        self::init();

        self::$engine->add($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @throws exception\Premission
     */
    public static function get($name) {
        self::init();

        return self::$engine->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @throws exception\Premission
     */
    public static function del($name) {
        self::init();

        self::$engine->del($name);
    }

    /**
     * @param $name
     * @param $value
     * @throws exception\Premission
     */
    public static function clear($name = '') {
        self::init();

        self::$engine->clear($name);
    }

    public static function changeEngine($name) {
        self::setEngine($name);
    }

    public static function endChange() {
        self::$engine = null;
    }
}