<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-10
 * Time: 下午10:04
 */

namespace app\index\controller;


use yang\Controller;

class Index extends Controller {

    public $prefixLengthsPsr4 = [], $prefixDirsPsr4 = [];

    public function app() {
        echo 'Hello Yang Framework';
    }

    public function index() {
        echo $this->display('index', ['name' => '洋洋', 'app' => ['app' => [1,2,3,4,5]], 'test' => true]);
    }
}