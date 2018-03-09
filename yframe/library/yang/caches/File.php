<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 18-1-4
 * Time: 下午5:04
 */

namespace yang\caches;


class File implements CacheServer
{
    static private $interface;

    public static function init()
    {
        // TODO: Implement init() method.
        if (empty(self::$interface)) {
            self::$interface = new static();
        }
        return self::$interface;
    }

    public function createName($name)
    {
        $name = trim($name, '.');
        if (strpos($name, '.')) {
            $name = explode('.', $name);
            $name = array_splice($name, 0, 3);
            $name[2] = md5($name[2]);
            $name = '/' . implode('/', $name);
        } else {
            $name = md5($name);
        }
        return \yang\Env::get('cache_path') . $name . '.php';
    }

    public function save($filename, $value) {
        $content = "<?php ";
    }

    public function add($name, $value)
    {
        // TODO: Implement add() method.
        //

    }

    public function clear($name = '')
    {
        // TODO: Implement clear() method.
        //
    }

    public function del($name)
    {
        // TODO: Implement del() method.
        //
    }
}