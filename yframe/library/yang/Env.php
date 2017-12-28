<?php
/**
 * Author: yangyang
 * Date  : 17-12-25
 * Time  : 下午6:17
 */

namespace yang;

/**
 * Class Env
 * @package yang
 * 环境变量控制 不再使用常量作为环境变量
 */
class Env
{
    public static $instrace;
    public function __construct()
    {
        return $this;
    }

    /**
     * 初始化Env
     * @return static
     */
    static public function run() {
        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        return self::$instrace;
    }

    /**
     * 设置环境变量
     * @param string $name
     * @param string $value
     */
    public function set($name, $value) {
        if (!empty($value)) {
            // strtoupper 参数是字符串 将所有的字母转换成大写
            // 还有个 strtolower 同样一个参数 将字符串里的字母全部转换成小写
            // 还有 ucfirst 第一个字母大写 一个参数 字符串
            // lcfirst 第一个字母小写 同上
            // trim ltrim rtrim 分别是 清除两边指定字符 清除左边指定字符 清除右边指定字符
            // 第一个参数是字符串 第二个是要清除的 默认是 空号换行等空字符
            $name = strtoupper($name);
            // putenv 内置函数 添加环境变量到php环境里
            // 运行完会被销毁 可以全局调用 参数是一个字符串 字符串的结构是 环境变量名=环境变量的值
            // 注意: =左右不能有空格 当=后面是空 例如 APP= 这种结构就是 销毁这个环境变量
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
        }
    }

    /**
     * 获取环境变量
     * @param string $name
     * @return array|false|string
     */
    public function get($name) {
        $name = strtoupper($name);
        // $_ENV 这个要开启了这个E才能用 忘记了 所以环境变量操作里 我们要使用两个 ENV 和 内置的函数
        // dotenv 这个是第三方的一个支持env文件和env操作的 它里面有个 $_SERVER来设置 尽量不用这个]
        // $_SERVER很慢 以后教怎么测试快慢
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }
        // getenv 获取环境变量 只有一个参数 环境变量的名字
        return getenv($name);
    }

    /**
     * 删除环境变量
     * @param string $name
     */
    public function del($name) {
        $name = strtoupper($name);
        if (isset($_ENV[$name])) {
            // unset 销毁变量或数组元素 可以有无数个参数 输入的参数乳沟是$array[key] 销毁这个键值和对应的value 如果是变量 就销毁变量
            unset($_ENV[$name]);
        }
        putenv($name . '=');
    }

    /**
     * 静态调用
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        // 这里讲下 __callStatic这个方法 和之前讲过的__call模式方法一样
        // 这个魔法方法是针对静态方法的 如果不存在就执行这个 可以看__call的说明
        // call_user_func_array 这个方法是 执行用户函数 第一个参数值是方法名 class的话如下
        // 第二个参数是 array 这里的array的每一个元素 也就是下标0 1 2 3 4等等 就代表这个方法的第一个参数 第二个参数等等
        // 同样的还有个 call_user_func 这个第一个参数和上面的一样 第二个参数到第n个 代表 被执行的函数的第一个参数 到第N个参数
        return call_user_func_array([self::$instrace, $name], $arguments);
    }
}