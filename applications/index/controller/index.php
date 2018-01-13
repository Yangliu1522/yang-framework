<?php
/**
 * Created by PhpStorm.
 * User: yyang
 * Date: 18-1-10
 * Time: 下午10:04
 */

namespace app\index\controller;


class Index {

    public $prefixLengthsPsr4 = [], $prefixDirsPsr4 = [];

    public function app() {
        echo 'Hello Yang Framework';
    }

    public function index() {
        $html ='isset($app["app"]) === app_app:';

        $this->forCommand($html);

        // echo \yang\View::fetch('index', ['name' => '洋洋', 'app' => ['app' => [1,2,3,4,5]]]);
    }

    public function forCommand($content = ''){
        return preg_replace_callback('/(?![\$\'">])[a-zA-Z0-9_](?>\w*)+(?![\("\'\=\+\-\#\%\/\[\{|,\?])/is', function ($match) {
            \yang\App::dump($match);
        }, $content);
    }
}