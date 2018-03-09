<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:48
 */

namespace yang\template;

// 自定义函数
use yang\App;
use yang\Log;

trait SimParse
{
    public $namespace = __NAMESPACE__ . '\\tplfunc\\';
    /**
     * 扩展接口实现
     */
    public function fallCallback($content){
        $reg = '/\{%(?:[\s])([\w\._\:]*)(?>(.*?)(?:[\s])%})|(?:[\s])(end[\w\._\:]*?)(?:[\s])%\}/is';
        $end = '';
        return preg_replace_callback($reg, function ($r) use (&$end) {
            if (strpos(trim($r[1]), 'end') === 0) {
                $content = $end;
                return $content;
            }
            $replace = $this->parseCall($r[1], $r[2], '####CONTENT####');
            if (strpos($replace, '####CONTENT####') !== false) {
                $replace = explode('####CONTENT####', $replace);
                $end = end($replace);
                $replace = $replace[0];
            }
            return $replace;
        }, $content);
    }

    public function parseCall($name, $argstring, $content) {
        if (strpos($name, ':') !== false) {
            list($class, $name) = explode(':', $name, 2);
        }

        if ($this->loadTag[$class]) {
            $class = $this->loadTag[$class];
        } else {
            $class = isset($class) ? $class :'cli';
            $class = $this->namespace . $class;
        }
        return call_user_func([new $class, $name], $argstring, $content);
        // return '<?php findstr("' .$tag. '"' . $content .');';
    }
}