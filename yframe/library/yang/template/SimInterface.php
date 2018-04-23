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
        if (strpos($var, '@') === 0) {
            return $this->parseSin($var);
        }

        if (strpos($var, '|')) {
            $end = explode('|', $var, 2);
            $var = $end[0];
            $end = '|' . end($end);
        }

        if (strpos($var, '.') !== false) {
            $var = preg_replace_callback('/(?![\$])(?:[\'"\s]*)[a-zA-Z_](?>\w*)(?:[\.][0-9a-zA-Z_](?>\w*))+/i', function ($m) {
                logs('p', $m);
                if ($this->cahce[$m[0]]) {
                    return $this->cahce[$m[0]];
                }

                if (trim($m[0]) == 'true' || trim($m[0]) == 'false' || $this->findinstart($m[0], '"\'')) {
                    return $m[0];
                }

                if (strpos($m[0], '.') === false) {
                    return '$' . $m[0];
                }

                $m[1] = explode('.', $m[0]);
                $name = '$' . trim(array_shift($m[1]));

                if ($this->isType == 'object') {
                    foreach ($m[1] as &$p) {
                        $p = "->{$p}";

                    }
                } else {
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
                }

                $this->cahce[$m[0]] = $name . implode('', $m[1]);
                return $this->cahce[$m[0]];
            }, $var);
        } else {
            $var = preg_replace_callback('/(?![\$])(?:["\'\s]*)[a-zA-Z_](?>\w*)/is', function ($m) {
                if (trim($m[0]) == 'true' || trim($m[0]) == 'false' || $this->findinstart($m[0], '"\'')) {
                    return $m[0];
                }
                $this->cahce[$m[0]] = '$' . trim($m[0]);
                return '$' . trim($m[0]);
            }, $var);
        }
        return $var . $end;
    }

    protected function parseSin($var) {
        $var = trim($var);

        $var = preg_replace_callback('/@([a-zA-Z_#](?>\w*))\(([\w\W]*?)\)/is', function ($m) {
            $function_name = $m[1];
            $var = $this->parseVar($m[2]);
            return $function_name . "({$var})";
        }, $var);

        return $var;
    }

    protected function parseTercode($var) {
        $var = trim($var);
        if (strpos($var, '??')) {
            if (PHP_VERSION > 7.0) {
                return $var;
            }

            list($temp1, $temp2) = explode('??', $var);
            return "{$temp1} ? {$temp1} : {$temp2}";
        }
        return $var;
    }

    private function findinstart($context, $search) {
        $context = trim($context);
        for ($i = 0; $i < strlen($search); $i ++) {
            if (strpos($context, $search[$i]) === 0) {
                return true;
            }
        }
        return false;
    }

    protected function parseFunc($var) {

        $var = $this->parseTercode($var);
        $functions = [];

        if (strpos($var, '|')) {
            $functions = explode('|', $var, 2);
            $var = array_shift($functions);

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
        }

        return $var;
    }

    protected function parseCondition($condition) {
        return str_replace([
            ' and ', ' not ', ' or ', ' is ',
        ], [' && ',' != ',' || ', ' == '], $condition);
    }

    protected function parseConditionVar($condition) {
        return preg_replace_callback('/(?![\'\$"])(?:[\s\-\>]*)[a-zA-Z_](?>\w*)+(?![\("\'\=\+\-\#\%\/\[\{\|\,\?])/is', function ($match) {
            if ($this->cahce[$match[0]]) {
                return $this->cahce[$match[0]];
            }

            if (strpos($match[0], '>') !== false) {
                return $match[0];
            }

            if (in_array($match[0], ['true','false'])) {
                return trim($match[0], '$');
            }
            $this->cahce[$match[0]] = '$' . trim($match[0]);
            return $this->cahce[$match[0]];
        }, $condition);
    }
}