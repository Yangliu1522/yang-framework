<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-13
 * Time: 上午2:57
 */

namespace yang;


class Controller {
    protected $request;

    public function __construct()
    {
        $this->request = Request::create();
    }

    public function display($name, $assign = []) {
        return View::fetch($name, $assign);
    }

    public function assign($name, $value) {
        View::assign($name, $value);
    }

    final public function success($msg = 'success', $url = '', $sec = 5)
    {
        $type = '(^_^)';
        $color = '43AEFA';
        $go = "立即跳转";
        include Env::get('root_path') . "tpl/redirect.php";
    }

    final public function error($msg = 'error', $url = '', $sec = 5)
    {
        $type = '(T_T)';
        $color = 'fa2f22';
        $go = "立即跳转";
        include Env::get('root_path') . "tpl/redirect.php";
    }

    final public function unknow($msg = 'error', $url = '', $sec = 5)
    {
        $type = '(?_?)';
        $color = 'fafa25';
        $go = "立即跳转";
        include Env::get('root_path') . "tpl/redirect.php";
    }
}