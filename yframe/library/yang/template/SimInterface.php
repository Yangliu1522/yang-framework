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
    protected $cahce = [], $cache_all = [];

    protected function parseVar($var, $use = false) {
        $var = trim($var);
        $end = '';
        if (strpos($var, '|')) {
            $end = explode('|', $var, 2);
            $var = $end[0];
            $end = '|' . end($end);
        }

        if (strpos($var, '.') !== false) {
            $var = preg_replace_callback('/[a-zA-Z_](?>\w*)(?:[\.][0-9a-zA-Z_](?>\w*))+/i', function ($m) {
                if (isset($this->cahce[$m[0]])) {
                    return $this->cahce[$m[0]];
                }
                if (strpos($m[0], '.') === false) {
                    return '$' . $m[0];
                }
                $m[1] = explode('.', $m[0]);
                $name = '$' . array_shift($m[1]);

                foreach ($m[1] as &$p) {
                    if (strpos($p, '[\'') === 0 || strpos($p, '\']') !== false) {
                        continue;
                    }
                    if (strpos($p, '[') === 0) {
                        $p = '[$' . trim($p, '[');
                        continue;
                    }

                    $p = "['{$p}']";
                }
                $this->cahce[$m[0]] = $name . implode('', $m[1]);
                return $this->cahce[$m[0]];
            }, $var);
        } else {
            if ($use === false) {
                $var = '$' . $var;
            }
        }
        return $var . $end;
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
                if (strpos($functions, '(') === false) {
                    $var = "$functions( {$var} )";
                } else {
                    $var = str_replace(['###'], [$var], $functions);
                }
            } else {
                $functions = explode('|', $functions);
                foreach ($functions as $fun) {
                    if (strpos($fun, '###')) {
                        $var = str_replace(['###'], [$var], $fun);
                        continue;
                    }
                    $var = "$fun({$var})";
                }
            }
        }

        return $var;
    }

    protected function parseCondition($condition) {
        return str_replace([
            ' and ', ' not ', ' or ', ' is ',
        ], [' && ',' != ',' || ', ' == '], $condition);
    }

    protected function parseConditionVar($condition) {
        return preg_replace_callback('/(?![\$\'">])[a-zA-Z0-9_](?>\w*)+(?![\("\'\=\+\-\#\%\/\[\{|,\?])/is', function ($match) {
            if (isset($this->cahce[$match[0]])) {
                return $this->cahce[$match[0]];
            }
            if (in_array($match[0], ['true','false'])) {
                return trim($match[0], '$');
            }
            $this->cahce[$match[0]] = '$' . trim($match[0]);
            return $this->cahce[$match[0]];
        }, $condition);
    }
}