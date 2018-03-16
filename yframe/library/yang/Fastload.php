<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-10
 * Time: 下午10:15
 */

namespace yang;

/**
 * Class Fastload
 * @package yang
 */
class Fastload
{

    private static $prefixAll = [];
    private static $prefixDir = [];
    private static $all = [];
    private $classmap = [], $missclass = [];
    private static $instrace, $class_alias = [];

    /**
     * 创建实例化
     * @return static
     */
    public static function create()
    {
        if (empty(self::$instrace)) {
            self::add('yang\\', dirname(__FILE__));
            self::$instrace = new static();
        }
        return self::$instrace;
    }

    /**
     * 修改已经保存的自动加载设置
     * @param $pre
     * @param string $path
     */
    public static function set($pre, $path = '')
    {
        if (!$pre) {
            self::$all = [$path];
        } else {
            $length = strlen($pre);
            if ($pre[$length - 1] !== '\\') {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            self::$prefixAll[$pre[0]][$pre] = $length;
            self::$prefixDir[$pre] = $path;
        }
    }

    /**
     * 注册alise
     * @param $pre
     * @param string $path
     */
    public static function alise($flag, $class = '')
    {
        if (is_array($flag)) {
            foreach ($flag as $name => $classname) {
                self::$class_alias[$name] = $classname;
            }
        } else {
            self::$class_alias[$flag] = $class;
        }
    }

    /**
     * 添加自动加载
     * @param $pre
     * @param string $path
     */
    public static function add($pre, $path = '')
    {
        if (!$pre) {
            if (!is_array($path)) $path = [$path];
            self::$all = array_merge(self::$all, $path);
        } else {
            $length = strlen($pre);
            if ($pre[$length - 1] !== '\\') {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            self::$prefixAll[$pre[0]][$pre] = $length;
            self::$prefixDir[$pre][] = $path;
        }
    }

    /**
     * 监听自动加载
     */
    public static function listen()
    {
        spl_autoload_register([self::create(), 'load'],true, true);
    }

    /**
     * 加载class 自动加载方法
     * @param $class
     * @return bool
     */
    public function load($class)
    {
        // 转换类名为大写, 规范
        if (preg_match('/\\\([a-z0-9]+)$/', $class)) {
            $class = preg_replace_callback('/\\\([a-z0-9]+)$/', function ($match) {
                return '\\' . ucfirst($match[1]);
            }, $class);
        }

        if (isset(self::$class_alias[$class])) {
            return class_alias(self::$class_alias[$class], $class);
        }

        if ($file = $this->find($class)) {
            self::includeFile($file);
            return true;
        }
    }

    /**
     * 基础处理
     * @param $class
     * @return bool|mixed|string
     */
    private function find($class)
    {
        if (isset($this->classMap[$class])) {
            return $this->classmap[$class];
        }

        if (isset($this->missclass[$class])) {
            return false;
        }

        $file = $this->findFile($class);

        if ($file === false) {
            $this->missclass[$class] = true;
            return false;
        }
        $this->classmap[$class] = $file;
        return $file;
    }

    /**
     * 查找文件
     * @param $class
     * @param string $ext
     * @return string
     */
    private function findFile($class, $ext = '.php')
    {
        // 初始化文件
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;
        // 检查目录
        $first = $class[0];
        if (isset(self::$prefixAll[$first])) {
            $subPath = $class;
            while (false !== ($last = strrpos($subPath, '\\'))) {
                $subPath = substr($subPath, 0, $last);
                $search = $subPath . '\\';
                if (isset(self::$prefixDir[$search])) {
                    foreach (self::$prefixDir[$search] as $dir) {
                        $length = self::$prefixAll[$first][$search];
                        // 检查文件是否存在
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }
    }

    /**
     * 引入文件, 全局
     * @param $file
     */
    public static function includeFile($file, $assign = [])
    {
        global $_W;
        if (!empty($assign)) {
            extract($assign);
        }
        include $file;
    }

    /**
     * 引入文件并返回文件内容, 全局
     * @param $file
     * @return mixed
     */
    public static function getContentOfFile($file)
    {
        return include $file;
    }
}

