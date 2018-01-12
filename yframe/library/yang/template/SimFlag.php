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
    public function foreachCommand($content = ''){
        return preg_replace_callback('/@for(?:[\s])([\w\W]*?)in(?:[\s])([\w\W]*?)(?:[\s]*)\:|@endfor;/is', function ($match) {
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
            return '<?php if(is_array('.$match[2].')): $__LIST__ = ' . $match[2] . ';foreach ($__LIST__ as ' .$match[1]. '): ?>';
        }, $content);
    }

    /**
     * for方法
     * @param string $content
     * @return mixed
     */
    public function forCommand($content = ''){

    }

    /**
     * 设置变量
     * @param string $content
     * @return mixed
     */
    public function setCommand($content = ''){

    }

    /**
     * 显示变量
     * @param string $content
     * @return mixed
     */
    public function showVar($content = ''){
        return preg_replace_callback('/\{\{(?:[\s])(.*?)(?:[\s])\}\}/i', function ($m) {
            return '<?php echo ' . trim($this->parseFunc($this->parseVar($m[1]))) . '; ?>';
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