<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-11
 * Time: 上午5:08
 */

namespace yang;


use yang\template\TplLib;

class View
{
    static private $view, $tpl;
    private $tpl_path;
    private static function create() {
        if (empty(self::$view)) {
            self::$view = new static();
        }
        return self::$view;
    }

    public static function fetch($name, $assign = []) {
        self::load($name);
        ob_start();
        self::out();
        $content = ob_get_clean();

        return $content;
    }

    private static function load($name) {
        list($tpl_path, $tpl) = self::create()->createFile($name);
        self::$tpl = Template::load($tpl, $tpl_path);
    }

    private static function out() {
        self::$tpl->render();
    }

    protected function createFile($name = '') {
        if ('' != pathinfo($name, PATHINFO_EXTENSION)) {
            return $name;
        }

        $req = \yang\Request::create();

        $module = $req->module();

        if (strpos($name, '@')) {
            list($module, $name) = explode('@', $name);
        }

        if (Env::get('tpl_path') == '') {
            $tpl_path = rtrim(Env::get('app_path'), '/') . '/' . $module . "/view/";
        } else {
            $tpl_path = trim(Env::get('tpl_path')) . rtrim(Env::get('app_path'), '/') . '/';
        }

        if (strpos($name, '/') === false) {

            $name = str_replace(['/', ':'], '/', $name);
            if (empty($name)) {
                $name = $req->controller() . '/' . $req->action();
            } else {
                $name = $req->controller() . '/' . $name;
            }
        } else {
            $name = str_replace(['/', ':'], '/', $name);
        }

        return [$tpl_path, $tpl_path . $name . '.html'];
    }

}