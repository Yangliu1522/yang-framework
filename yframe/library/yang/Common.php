<?php
/**
 * Created by PhpStorm.
 * User: y_yang
 * Date: 18-1-26
 * Time: 上午10:36
 */

namespace yang;


class Common
{
    public static $app_debug = true;

    public static function path2url($path) {
        $root2 = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $base2 = str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['DOCUMENT_ROOT']);
        return str_replace($base2, '', $root2);
    }

    /**
     * @param mixed
     */
    public static function dump()
    {
        $str = func_get_args();
        foreach ($str as $arrrorstring) {
            // $arrrorstring = htmlspecialchars($arrrorstring);
            $pre = print_r($arrrorstring, true);
            echo '<p><pre>' . $pre . '</pre></p>' . PHP_EOL;
        }
    }
}