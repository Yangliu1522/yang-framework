<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:48
 */

namespace yang\template;

// 自定义函数
trait SimParse
{
    /**
     * 扩展接口实现
     */
    public function fallCallback($content){
        $reg = '/\{%(?:[\s])([\w\._]*)(?>(.*?)(?:[\s])%})|(?:[\s])(end[\w\._]*?)(?:[\s])%\}/is';
        $end = '';
        $regex = '/\{%(?:[\s])([\w\._]*)\b(?>(?:(?!%\}).)*|\/(end[\w\._]*))(?:[\s])%\}/is';
        return preg_replace_callback($reg, function ($r) use (&$end) {
            if (strpos($r[1], 'end') === 0) {
                return $end;
            }
            $replace = $this->parseFindstr($r[2], '####CONTENT#####');
            $replace = explode('####CONTENT#####',$replace);
            $end = end($replace);
            return $replace[0];
        }, $content);
    }

    public function parseFindstr($tag, $content) {
        return '<?php findstr("' .$tag. '"' . $content .'); ?>';
    }
}