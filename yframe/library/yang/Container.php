<?php
/**
 * Created by PhpStorm.
 * User: y_yang
 * Date: 18-1-26
 * Time: 上午9:58
 */

namespace yang;

// 容器尝试
class Container
{
    private static $con_flag = [];
    private static $instance;
    private $instances = [];
    public static function register($name, $value = '') {
        if (is_array($name)) {
            foreach ($name as $flag => $classname) {
                self::$con_flag[$flag] = $classname;
            }
        } else {
            self::$con_flag[$name] = $value;
        }
    }

    /**
     * 创建引用
     * @return static
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function get($name, $vars = [], $establishNew = false) {
        Common::dump($name);
        return self::getInstance()->make($name, $vars, $establishNew);
    }

    public function make($name, $vars = [], $establishNew = false) {
        Common::dump($name);
        if (is_bool($vars)) {
            $establishNew = $vars;
            $vars = [];
        }

        if (isset($this->instances[$name]) && !$establishNew) {
            $object = $this->instances[$name];
        } else {
            if (isset(self::$con_flag[$name])) {
                $c = self::$con_flag[$name];

                if ($c instanceof \Closure) {
                    $object = $this->establishFunction($c, $vars);
                } else {
                    $object = $this->make($c, $vars, $establishNew);
                }
            } else {
                $object = $this->establishClass($name, $vars);
            }

            if (!$establishNew) {
                $this->instances[$name] = $object;
            }
        }

        return $object;
    }

    private function establishClass($name, $vars = []) {
        $name = new \ReflectionClass($name);
        $constructor = $name->getConstructor();
        $arr = $this->bindParam($constructor, $vars);
        return $name->newInstanceArgs($arr);
    }

    private function bindParam(\ReflectionMethod $reflect, $vars = []) {

        $args = [];

        if ($reflect->getNumberOfParameters() > 0) {
            $params = $reflect->getParameters();

            foreach ($params as $param) {
                $name = $param->getName();
                $class = $param->getClass();

                if (!empty($class)) {
                    $args[] = $this->make($class);
                } else if (isset($vars[$name])) {
                    $args[] = $vars[$name];
                } else if (!empty($param->isDefaultValueAvailable())) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new \InvalidArgumentException('The name of this parameter loss is :' . $name);
                }
            }
        }

        return $args;
    }

    private function establishFunction($name, $vars = []) {
        $name = new \ReflectionFunction($name);
        // = $name->getconstructor();
        Common::dump($name);
        return $name;
    }
}