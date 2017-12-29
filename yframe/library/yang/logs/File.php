<?php
/**
 * Author: yangyang
 * Date  : 17-12-29
 * Time  : 下午7:20
 */

namespace yang\logs;


class File implements LogServe
{
    public $logs = [];

    public static function init()
    {
        // TODO: Implement init() method.
        return new static();
    }
    /**
     * 返回Log的生成语句
     * @return string
     */
    public function save($content = '')
    {
        $time = time();
        $path = date('Ym', $time);
        $path = \yang\Env::get('cache_path') . $path . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file = date('dH', $time) . '.log';
        file_put_contents($path . $file, $content, FILE_APPEND);
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }
}