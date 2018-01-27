<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:47
 */

namespace yang\template;

// 指令实现
use yang\App;

trait SimFlag
{
    use SimParse;
    /**
     * foreach方法
     * @param string $content
     * @return mixed
     */
    public function foreachCommand($content = ''){
        return preg_replace_callback('/@for(?:[\s])([\w\W]*?)in(?:[\s])([\w\W]*?)(?:[\s]*)\:|@endfor;/is', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            if (strpos($match[0],'@endfor') === 0) {
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
            $return = '<?php if(is_array('.$match[2].')): $__LIST__ = ' . $match[2] . ';foreach ($__LIST__ as ' .$match[1]. '): ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * for方法
     * @param string $content
     * @return mixed
     */
    public function forCommand($content = ''){
        return preg_replace_callback('/@for(?:[\s])([\w\W]*?)as(?:[\s])([\d]+)\.([<\d>]*)\.([\d]+?)(?:[\s]*)do|@endfor;/is', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            if (strpos($match[0],'@endfor') === 0) {
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
            $return = '<?php' . " for ({$var} = {$left};{$var}{$ar}{$right};{$var}{$c};):". ' ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * 设置变量
     * @param string $content
     * @return mixed
     */
    public function setCommand($content = ''){
        return preg_replace_callback('/@var(?:[\s]*)([\w\W]*?);/i', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            $condition = '""';
            if (strpos($match[1], '=')) {
                $var = explode('=', $match[1]);
                $condition = end($var);
                $var = $this->parseVar($var[0]);
            } else {
                $var = $this->parseVar($match[1]);
            }

            $return = '<?php ' . $var . ' = ' . $condition . '; ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * if elseif else 语句
     * @param string $content
     */
    public function ifCommand($content = '') {
        return preg_replace_callback('/@if(?:[\s])(.*?):|@elseif(?:[\s])(.*?):|@else:|@endif;/is', function ($match) {
            if (isset($this->cahce_all[md5($match[0])])) {
                return $this->cahce_all[md5($match[0])];
            }
            if (strpos($match[0],'@endif') === 0) {
                return '<?php endif; ?>';
            }
            if (strpos($match[0],'@else:') === 0) {
                return '<?php else: ?>';
            }
            if (strpos($match[0],'@else') === 0) {
                $condition = $this->parseVar($match[2], true);
                $condition = $this->parseCondition($condition);
                $condition = $this->parseConditionVar($condition);
                $return = '<?php elseif ('.$condition.'): ?>';
                $this->cahce_all[md5($match[0])] = $return;
                return $return;
            }
            $condition = $this->parseVar($match[1], true);
            $condition = $this->parseCondition($condition);
            $condition = $this->parseConditionVar($condition);
            $return = '<?php if ('.$condition.'): ?>';
            $this->cahce_all[md5($match[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * 显示变量
     * @param string $content
     * @return mixed
     */
    public function showVar($content = ''){
        return preg_replace_callback('/\{\{(?:[\s])(.*?)(?:[\s])\}\}/i', function ($m) {
            if (isset($this->cahce_all[md5($m[0])])) {
                return $this->cahce_all[md5($m[0])];
            }
            $return = '<?php echo htmlentities(' . trim($this->parseFunc($this->parseVar($m[1]))) . '); ?>';
            $this->cahce_all[md5($m[0])] = $return;
            return $return;
        }, $content);
    }

    /**
     * 显示函数
     * @param string $content
     * @return mixed
     */
    public function showFunc($content = ''){

    }
}