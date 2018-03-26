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
    static private $view, $tpl, $assign = [];
    private $tpl_path;
    private static function create() {
        if (empty(self::$view)) {
            self::$view = new static();
        }
        return self::$view;
    }

    public static function fetch($name, $assign = []) {
        self::load($name);
        self::assign($assign);
        ob_start();
        self::out();
        $content = ob_get_clean();

        self::$tpl = '';
        self::$assign = [];
        return $content;
    }

    public static function assign($name, $value = '') {
        if (!empty($name)) {
            if (is_array($name)) {
                self::$assign = array_merge(self::$assign, $name);
            } else {
                self::$assign[$name] = $value;
            }
        }
    }
    private static function load($name) {
        list($tpl_path, $tpl) = self::create()->createFile($name);
        self::$tpl = Template::load($tpl, $tpl_path);
    }

    private static function out() {
        extract(self::$assign);
        Fastload::includeFile(self::$tpl->render(), self::$assign);
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
            $tpl_path = rtrim(Env::get('tpl_path'), '/') . '/' . $module . '/';
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