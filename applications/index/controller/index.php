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
}