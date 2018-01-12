<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:48
 */

namespace yang\template;

// 规定模仿接口需要实现的功能
abstract class SimInterface
{
    protected $cache = [];
    /**
     * foreach方法
     * @param string $content
     * @return mixed
     */
    abstract public function foreachCommand($content = '');

    /**
     * for方法
     * @param string $content
     * @return mixed
     */
    abstract public function forCommand($content = '');

    /**
     * 设置变量
     * @param string $content
     * @return mixed
     */
    abstract public function setCommand($content = '');

    /**
     * 显示变量
     * @param string $content
     * @return mixed
     */
    abstract public function showVar($content = '');

    /**
     * 显示函数
     * @param string $content
     * @return mixed
     */
    abstract public function showFunc($content = '');
    /**
     * 扩展接口实现
     */
    abstract public function fallCallback($content);

    protected function parseVar($var) {
        $var = trim($var);

        if (strpos($var, '.') !== false) {
            $var = preg_replace_callback('/[a-zA-Z_](?>\w*)(?:[:\.][0-9a-zA-Z_](?>\w*))+/i', function ($m) {
                if (strpos($m[0], '.') === false) {
                    return '$' . $m[0];
                }
                $m[0] = explode('.', $m[0]);
                $name = '$' . array_shift($m[0]);

                foreach ($m[0] as &$p) {
                    if (strpos($p, '[\'') === 0 || strpos($p, '\']') !== false) {
                        continue;
                    }
                    if (strpos($p, '[') === 0) {
                        $p = '[$' . trim($p, '[');
                        continue;
                    }

                    $p = "['{$p}']";
                }
                return $name . implode('', $m[0]);
            }, $var);
        } else {
            $var = '$' . $var;
        }

        return $var;
    }

    protected function parseFunc($var) {

        $functions = [];

        if (strpos($var, '|')) {
            $functions = explode('|', $var, 2);
            $var = array_shift($functions);
        }

        if (!empty($functions)) {
            $functions = $functions[0];
            if (strrpos($functions, '|') === false) {
                $var = "$functions( {$var} )";
            } else {
                $functions = explode('|', $functions);
                foreach ($functions as $fun) {
                    if (strpos($fun, '=')) {
                        $fun = str_replace(['=', '###'], ['(',$var], $fun);
                        $var = $fun . ')';
                        continue;
                    }
                    $var = "$fun({$var})";
                }
            }
        }

        return $var;
    }
}