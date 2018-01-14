<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-14
 * Time: 下午2:59
 */

namespace yang\template\tplfunc;


use yang\template\SimInterface;

class Cli extends SimInterface
{

    public function isfunc($argstring, $content = '') {
        return '<?php echo "'. $argstring .$content.'";?>';
    }
}