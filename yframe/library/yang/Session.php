<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 17-12-8
 * Time: 下午8:04
 */

namespace yang;


class Session {

    private $session_status;
    static private $session;
    public function __construct()
    {
        $status = session_status();
        if ($status === PHP_SESSION_ACTIVE) {
            if (Config::get('session_life') !== 0) {
                $val = Config::get('session_life');

                setcookie("PHPSESSID", session_id(), time() + intval($val));
            }
            $this->session_status = true;
        } else {
            if (Config::get('session_life') !== 0) {
                session_set_cookie_params(Config::get('session_life'));
            }
            $this->session_status = session_start();
        }
        $this->prefix = Config::get('session_prefix');
        return $this;
    }

    static public function instrace() {
        if (empty(self::$session)) {
            self::$session = new static();
        }
        return self::$session;
    }

    public function get($name) {
        if (strpos($name, '.')) {
            list($pname, $name) = explode('.', $name);
            if (!empty($this->prefix)) {
                return $_SESSION[$this->prefix][$pname][$name];
            }
            return $_SESSION[$pname][$name];
        }
        if (!empty($this->prefix)) {
            return $_SESSION[$this->prefix][$name];
        }
        return $_SESSION[$name];
    }

    public function set($name, $value) {
        if (strpos($name, '.')) {
            list($pname, $name) = explode('.', $name);
            if (!empty($this->prefix)) {
                $_SESSION[$this->prefix][$pname][$name] = $value;
            } else {
                $_SESSION[$pname][$name] = $value;
            }
        } else {
            if (!empty($this->prefix)) {
                $_SESSION[$this->prefix][$name] = $value;
            } else {
                $_SESSION[$name] = $value;
            }
        }
    }

    public function has($name) {
        if (strpos($name, '.')) {
            list($pname, $name) = explode('.', $name);
            if (!empty($this->prefix)) {
                return isset($_SESSION[$this->prefix][$pname][$name]);
            }
            return isset($_SESSION[$pname][$name]);
        }
        if (!empty($this->prefix)) {
            return isset($_SESSION[$this->prefix][$name]);
        }
        return isset($_SESSION[$name]);
    }

    public function del($name) {
        if (strpos($name, '.')) {
            list($pname, $name) = explode('.', $name);
            if (!empty($this->prefix)) {
                unset($_SESSION[$this->prefix][$pname][$name]);
            }
            unset($_SESSION[$pname][$name]);
        }
        if (!empty($this->prefix)) {
            unset($_SESSION[$this->prefix][$name]);
        }
        unset($_SESSION[$name]);
    }

    public function all() {
        if (!empty($this->prefix)) {
            return $_SESSION[$this->prefix];
        }
        return $_SESSION;
    }
}