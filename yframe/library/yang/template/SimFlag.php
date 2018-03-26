<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:47
 */

namespace yang\template;

// 指令实现

trait SimFlag
{
    use SimParse;

    /**
     * foreach方法
     * @param string $content
     * @return mixed
     */
    public function foreachCommand($content = '')
    {
        return preg_replace_callback('/@for(?:[\s])([\w\W]*?)in(?:[\s])([\w\W]*?)(?:[\s]*)\:|@endfor;/is', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            if (strpos($match[0], '@endfor') === 0) {
                return '<?php endforeach;endif; ?>';
            }
            if (strpos($match[1], ',')) {
                $match[1] = explode(',', $match[1]);
                foreach ($match[1] as &$v) {
                    $v = trim($v);
                }
                $match[1] = implode(' => $', $match[1]);
            }
            $match[1] = '$' . trim($match[1]);
            $match[2] = $this->parseVar($match[2]);
            // 转换变量
            //end
            $return = '<?php if(is_array(' . $match[2] . ') || ' .$match[2]. ' instanceof \yang\model\Result): $__LIST__ = ' . $match[2] . ';foreach ($__LIST__ as ' . $match[1] . '): ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * for方法
     * @param string $content
     * @return mixed
     */
    public function forCommand($content = '')
    {
        return preg_replace_callback('/@for(?:[\s])([\w\W]*?)as(?:[\s])([\d]+)\.([<\d>]*)\.([\d]+?)(?:[\s]*)do|@endfor;/is', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            if (strpos($match[0], '@endfor') === 0) {
                return '<?php endfor; ?>';
            }
            $var = $this->parseVar($match[1]);
            $left = $match[2];
            $right = $match[4];
            $c = empty($match[3]) ? '++' : '= ' . trim($match[3], '<>');
            if (strpos($match[3], '>') !== false) {
                $c = '+' . $c;
                $ar = '<';
            } else {
                $c = '-' . $c;
                $ar = '>';
            }
            $return = '<?php' . " for ({$var} = {$left};{$var}{$ar}{$right};{$var}{$c};):" . ' ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * 设置变量
     * @param string $content
     * @return mixed
     */
    public function setCommand($content = '')
    {
        return preg_replace_callback('/@var\(([\w\W]*?),([\w\W]*?)\);/i', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            $condition = '""';
            $var = $this->parseVar($match[1]);
            $condition = $match[2];

            $return = '<?php ' . $var . ' = ' . $condition . '; ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * if elseif else 语句
     * @param string $content
     */
    public function ifCommand($content = '')
    {
        return preg_replace_callback('/@if(?:[\s])(.*?):|@elseif(?:[\s])(.*?):|@else:|@endif;/is', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            if (strpos($match[0], '@endif') === 0) {
                return '<?php endif; ?>';
            }
            if (strpos($match[0], '@else:') === 0) {
                return '<?php else: ?>';
            }
            if (strpos($match[0], '@else') === 0) {
                $condition = $this->parseVar($match[2], true);
                $condition = $this->parseCondition($condition);
                $condition = $this->parseConditionVar($condition);
                $return = '<?php elseif (' . $condition . '): ?>';
                $this->cahce_all[md5($match[0])] = $return;
                return $return;
            }
            $condition = $this->parseVar($match[1], true);
            $condition = $this->parseCondition($condition);
            $condition = $this->parseConditionVar($condition);
            $return = '<?php if (' . $condition . '): ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * 显示变量
     * @param string $content
     * @return mixed
     */
    public function showVar($content = '')
    {
        return preg_replace_callback('/\{\{(?:[\s])(.*?)(?:[\s])\}\}/i', function ($m) {
            if (isset($this->cahce_all[md5($m[0])])) {
                return $this->cahce_all[md5($m[0])];
            }
            $var = $this->parseVar($m[1]);;
            if (strpos($var, '!') === 0) {
                $return = '<?php echo ' . $this->parseFunc(trim($var, '!')) . '; ?>';
            } else {
                $return = '<?php echo htmlentities(' .  $this->parseFunc($var) . '); ?>';
            }
            $this->cahce_all[md5($m[0])] = $return;
            return $return;
        }, $content);
    }


    /**
     * 自定义命令加载命令
     */
    public function loadtagCommand($content = '')
    {
        return preg_replace_callback('/@loadtag\s+([\w\W]*?);/i', function ($match) {
            $req = \yang\Request::create();
            $module = $req->module();
            $name = trim($match[1]);
            if (strpos($name, '/')) {
                $name = explode('/', $name,2);
                $module = $name[0];
                $name = str_replace('/', '\\', end($name));
            }
            $class = '\\app\\' . $module . '\\taglib\\' . ucfirst($name);

            $this->loadTag[ucfirst($name)] = $class;
            return "<!-- Load TagLib -->";
        }, $content);
    }


    public function envCommand($content = '')
    {
        return preg_replace_callback('/@E\(([\w_\-\.]*?)\);/i', function ($match) {
            return \yang\Env::get($match[1]);
        }, $content);
    }

    public function configCommand($content = '')
    {
        return preg_replace_callback('/@C\(([\w_\-\.]*?)\);/i', function ($match) {
            return \yang\Config::get($match[1]);
        }, $content);
    }

    /**
     * 自定义函数
     * @param string $content
     * @return mixed
     */
    public function showFunc($content = '')
    {
        foreach ($this->loadTag as $name => $value) {
            $content = $this->userFunc($name, $value, $content);
            $content = $this->userFuncTag($name, $value, $content);
        }

        return $content;
    }

    private function userFunc($name, $class, $content = '')
    {
        $c = new $class();
        $content = preg_replace_callback('/@' . $name . '::(?P<method>[\w_]*)\((?P<args>.*?)\);/i', function ($match) use ($c) {
            preg_match_all('/(?:["\'])([\w\W]*?)(?:["\',])/', $match['args'], $args);
            $args = $args[1];
            $method = $match['method'] . 'Command';
            if (method_exists($c, $method)) {
                $content = call_user_func_array([$c, $method], $args);
                return $content;
            }
            return $match[0];
        }, $content);

        return $content;
    }

    private function userFuncTag($name, $class, $content = '')
    {
        $c = new $class();
        $break = "####CONTENT####";
        $end = '';
        $content = preg_replace_callback('/@' . $name . '::(?P<method>[\w_]*)\((?P<args>.*?)\):|@end;/i', function ($match) use ($c, &$end, $break) {
            if (!isset($match[1])) {
                return $end;
            }
            preg_match_all('/(?:["\'])([\w\W]*?)(?:["\',])/', $match['args'], $args);
            $args = $args[1];
            $method = $match['method'] . 'Tag';
            if (method_exists($c, $method)) {
                $end = explode($break, call_user_func([$c, $method], $args, $break));
                $m = $end[0];
                $end = $end[1];
                return $m;
            }
            return $match[0];
        }, $content);

        return $content;
    }
}